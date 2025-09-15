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
        $products = Product::all();
        $settings = Setting::first();
        $cashRegister = CashRegister::whereNull('closed_at')
            ->whereDate('opened_at', now()->toDateString())
            ->latest()
            ->first();

        return inertia('Transactions/Index', [
            'products' => $products,
            'settings' => $settings,
            'cashier' => auth()->user(),
            'cashRegister' => $cashRegister,
        ]);
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
            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil disimpan')
                ->with('print_struk', $trx->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
