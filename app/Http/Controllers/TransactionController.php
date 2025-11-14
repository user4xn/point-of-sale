<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TransactionItem;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\ProductUnitConversion;
use App\Models\CashRegisterTransaction;
use App\Models\BankTransaction;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
      $query = Transaction::with('user')
        ->when($request->search, fn($q) =>
            $q->where('invoice_number', 'like', "%{$request->search}%")
        )
        ->latest();

      $transactions = $query->paginate(10)->withQueryString();

      $todayTrx = Transaction::whereDate('created_at', today());
      $weekTrx = Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
      $monthTrx = Transaction::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
      $allTrx = Transaction::query();

      $calcPayment = function ($baseQuery) {
          return [
              'cash' => (clone $baseQuery)->where('payment_method', 'cash')->sum('grand_total'),
              'noncash' => (clone $baseQuery)->where('payment_method', 'bank')->sum('grand_total'),
          ];
      };

      $metrics = [
          'today' => [
              'total_transactions' => $todayTrx->count(),
              'total_sales'        => $todayTrx->sum('grand_total'),
              'total_items'        => TransactionItem::whereHas('transaction', fn($q) =>
                  $q->whereDate('created_at', today())
              )->sum('quantity'),
              'payment'            => $calcPayment(clone $todayTrx),
          ],
          'week' => [
              'total_transactions' => $weekTrx->count(),
              'total_sales'        => $weekTrx->sum('grand_total'),
              'payment'            => $calcPayment(clone $weekTrx),
          ],
          'month' => [
              'total_transactions' => $monthTrx->count(),
              'total_sales'        => $monthTrx->sum('grand_total'),
              'payment'            => $calcPayment(clone $monthTrx),
          ],
          'alltime' => [
              'total_transactions' => $allTrx->count(),
              'total_sales'        => $allTrx->sum('grand_total'),
              'payment'            => $calcPayment(clone $allTrx),
          ]
      ];

      $metrics['today']['avg_transaction'] =
          $metrics['today']['total_transactions'] > 0
              ? $metrics['today']['total_sales'] / $metrics['today']['total_transactions']
              : 0;

      return inertia('Transactions/Index', [
          'transactions' => $transactions,
          'dashboard' => $metrics,
          'filters' => $request->only('search'),
      ]);
    }

    public function detail($id)
    {
        $trx = Transaction::with(['items.product', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'transaction' => $trx
        ]);
    }

    public function cashier()
    {
        $cashRegister = CashRegister::whereNull('closed_at')
            ->latest()
            ->first();

        if (!$cashRegister) {
          return redirect()->route('dashboard')->with('flash', ['error' => 'Kas belum dibuka']);
        }
        if(date('Y-m-d', strtotime($cashRegister->opened_at)) != now()->toDateString()) {
          return redirect()->route('dashboard')->with('flash', ['error' => 'Terdapat kas yang tidak valid tetapi masih terbuka, harap tutup kas terlebih dahulu, lalu buka kas baru.']);
        }

        $settings = Setting::first();
        $cashRegister = CashRegister::whereNull('closed_at')
            ->whereDate('opened_at', now()->toDateString())
            ->latest()
            ->firstOrFail();

        return inertia('Transactions/Cashier', [
            'settings' => $settings,
            'cashier' => auth()->user(),
            'cashRegister' => $cashRegister,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('query');

        $products = Product::with('unitConversions')
            ->where('status', 'active')
            ->when($query, function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with('unit:id,name')
            ->limit(10)
            ->get([
                'id',
                'unit_id',
                'name',
                'sku',
                'sell_price',
                'stock',
            ]);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit_conversion_id' => 'nullable|exists:product_unit_conversions,id',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,bank',
        ]);

        DB::beginTransaction();

        try {
            $cashRegister = CashRegister::whereNull('closed_at')
                ->whereDate('opened_at', now()->toDateString())
                ->latest()
                ->first();

            if (!$cashRegister) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kas belum dibuka hari ini',
                ], 422);
            }

            $settings = Setting::first();
            $taxRate = $settings?->tax_rate ?? 0;

            $total = collect($request->items)
                ->sum(fn ($item) => $item['price'] * $item['quantity']);
            $discount = $request->discount ?? 0;
            $tax = ($total - $discount) * ($taxRate / 100);
            $grandTotal = $total - $discount + $tax;
            $change = $request->paid_amount - $grandTotal;

            if ($request->paid_amount < $grandTotal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang customer tidak mencukupi',
                ], 422);
            }

            $trx = Transaction::create([
                'customer_id'      => $request->customer_id,
                'invoice_number'   => 'INV-' . now()->format('Ymd-His'),
                'user_id'          => auth()->id(),
                'cash_register_id' => $cashRegister->id,
                'customer_name'    => $request->customer_name,
                'total_price'      => $total,
                'discount'         => $discount,
                'tax'              => $tax,
                'grand_total'      => $grandTotal,
                'paid_amount'      => $request->paid_amount,
                'change_amount'    => $change,
                'status'           => 'paid',
                'payment_method'   => $request->payment_method,
            ]);

            foreach ($request->items as $item) {
                $product = Product::with('unit')->findOrFail($item['product_id']);

                if ($product->status !== 'active') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Produk {$product->name} tidak aktif",
                    ], 422);
                }

                $conversionQty = 1;
                $price = $product->sell_price;
                $uc = null;

                if (!empty($item['unit_conversion_id'])) {
                    $uc = ProductUnitConversion::find($item['unit_conversion_id']);
                    if ($uc) {
                        $conversionQty = $uc->conversion;
                        $price = $uc->sell_price;
                    }
                }

                $deductStock = $item['quantity'] * $conversionQty;

                if ($product->stock < $deductStock) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok produk {$product->name} tidak cukup",
                    ], 422);
                }

                $product->decrement('stock', $deductStock);

                TransactionItem::create([
                    'transaction_id'      => $trx->id,
                    'product_id'          => $item['product_id'],
                    'unit_conversion_id'  => $item['unit_conversion_id'] ?? null,
                    'unit_name'           => $uc?->unit_name ?? $product->unit?->name,
                    'quantity'            => $item['quantity'],
                    'price'               => $price,
                    'subtotal'            => $price * $item['quantity'],
                ]);
            }

            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $earnedPoints = floor($grandTotal / 1000);
                    $customer->increment('points', $earnedPoints);
                }
            }

            if ($request->payment_method === 'cash') {
              $cashRegister->increment('total_sales');
              $cashRegister->increment('total_amount', $grandTotal);

              CashRegisterTransaction::create([
                  'cash_register_id' => $cashRegister->id,
                  'transaction_id'   => $trx->id,
              ]);   
            } else {
              $bankTransaction = BankTransaction::create([
                  'cash_register_id' => $cashRegister->id,
                  'transaction_id'   => $trx->id,
                  'amount'           => $grandTotal,
              ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'trx_id'  => $trx->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function print($id)
    {
        $trx = Transaction::with(['items.product.unit', 'user'])->findOrFail($id);
        $settings = Setting::first();

        $formatRp = fn ($val) => 'Rp ' . number_format((float)$val, 0, ',', '.');

        $trx->total_price   = $formatRp($trx->total_price);
        $trx->discount      = $formatRp($trx->discount);
        $trx->tax           = $formatRp($trx->tax);
        $trx->grand_total   = $formatRp($trx->grand_total);
        $trx->paid_amount   = $formatRp($trx->paid_amount);
        $trx->change_amount = $formatRp($trx->change_amount);

        $trx->items->transform(function ($item) {
            $item->price    = number_format((float)$item->price, 0, ',', '.');
            $item->subtotal = number_format((float)$item->subtotal, 0, ',', '.');
            return $item;
        });

        return inertia('Print/Transaction', [
            'trx' => $trx,
            'settings' => $settings,
        ]);
    }

    public function printDirect($id)
    {
        $trx = Transaction::with(['items.product.unit', 'user'])->findOrFail($id);
        $settings = Setting::first();

        $items = [];
        foreach ($trx->items as $item) {
            $items[] = [
                'name'     => $item->product->name,
                'qty'      => $item->quantity,
                'unit'     => $item->unit_name,
                'price'    => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ];
        }

        return response()->json([
            'store_name'   => $settings->store_name,
            'store_address'=> $settings->store_address,
            'store_contact'=> $settings->store_contact,
            'invoice'      => $trx->invoice_number,
            'date'         => $trx->created_at->format('d/m/Y H:i'),
            'cashier'      => $trx->user?->name,
            'items'        => $items,
            'total_price'  => (float) $trx->total_price,
            'discount'     => (float) $trx->discount,
            'tax'          => (float) $trx->tax,
            'grand_total'  => (float) $trx->grand_total,
            'paid_amount'  => (float) $trx->paid_amount,
            'change_amount'=> (float) $trx->change_amount,
            'footer'       => $settings->receipt_template,
        ]);
    }


    public function today()
    {
        $transactions = \App\Models\Transaction::withCount('items')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get(['id', 'invoice_number', 'grand_total', 'created_at']);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }
}

