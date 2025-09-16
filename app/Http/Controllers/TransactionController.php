<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TransactionItem;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;

class TransactionController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $cashRegister = CashRegister::whereNull('closed_at')
            ->whereDate('opened_at', now()->toDateString())
            ->latest()
            ->first();

        return inertia('Transactions/Index', [
            'settings' => $settings,
            'cashier' => auth()->user(),
            'cashRegister' => $cashRegister,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('query');
        $products = \App\Models\Product::query()
            ->where('sku', $query)
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'sku', 'sell_price', 'stock']);

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
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $cashRegister = CashRegister::whereNull('closed_at')
                ->whereDate('opened_at', now()->toDateString())
                ->latest()
                ->first();

            if (!$cashRegister) {
                return back()->with('error', 'Kas belum dibuka hari ini');
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
                throw new \Exception("Uang customer tidak mencukupi");
            }

            $trx = Transaction::create([
                'invoice_number' => 'INV-' . now()->format('Ymd-His'),
                'user_id' => auth()->id(),
                'cash_register_id' => $cashRegister->id,
                'customer_name' => $request->customer_name,
                'total_price' => $total,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $change,
                'status' => $grandTotal <= $request->paid_amount ? 'paid' : 'unpaid',
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak cukup");
                }

                $product->decrement('stock', $item['quantity']);

                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            $cashRegister->increment('total_sales');
            $cashRegister->increment('total_amount', $grandTotal);

            CashRegisterTransaction::create([
                'cash_register_id' => $cashRegister->id,
                'transaction_id' => $trx->id,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'trx_id' => $trx->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function print($id)
    {
        $trx = Transaction::with(['items.product', 'user'])->findOrFail($id);
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
}

