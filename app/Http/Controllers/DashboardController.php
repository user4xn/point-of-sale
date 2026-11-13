<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $transactionsTodayQuery = Transaction::whereDate('created_at', $today)->where('status', 'paid');

        $transactionsToday = $transactionsTodayQuery->get();

        $totalTransactions = $transactionsToday->count();
        $grossSales = $transactionsToday->sum('total_price');
        $netSales   = $transactionsToday->sum(fn ($transaction) => $transaction->total_price - $transaction->discount);
        $avgTransaction = $totalTransactions > 0
            ? round($netSales / $totalTransactions)
            : 0;

        $paymentToday = [
            'cash' => (clone $transactionsTodayQuery)
                ->where('payment_method', 'cash')
                ->sum('grand_total'),

            'noncash' => (clone $transactionsTodayQuery)
                ->where('payment_method', 'bank')
                ->sum('grand_total'),
        ];

        $topProducts = TransactionItem::query()
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('product_unit_conversions as puc', 'transaction_items.unit_conversion_id', '=', 'puc.id')
            ->select(
                'transaction_items.product_id',
                'products.name',
                'products.stock',
                DB::raw('SUM(transaction_items.quantity * COALESCE(puc.conversion, 1)) as qty_sold')
            )
            ->whereIn('transaction_id', $transactionsToday->pluck('id'))
            ->groupBy('transaction_items.product_id', 'products.name', 'products.stock')
            ->orderByDesc('qty_sold')
            ->limit(3)
            ->get();

        $stockAlerts = Product::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get(['id','name','stock']);

        $register = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        $currentCash = $register
            ? $register->opening_amount + (clone $transactionsTodayQuery)
                ->where('cash_register_id', $register->id)
                ->where('payment_method', 'cash')
                ->sum('grand_total')
            : 0;
        $currentBank = $paymentToday['noncash'];

        $ownerProps = Auth::user()?->role === 'admin'
            ? ['owner_summary' => app(\App\Services\OwnerSummary::class)->metrics()]
            : [];

        return Inertia::render('Dashboard', [
            ...$ownerProps,
            'dashboard' => [
                'total_transactions' => $totalTransactions,
                'gross_sales' => $grossSales,
                'net_sales' => $netSales,
                'avg_transaction' => $avgTransaction,
                'payment' => $paymentToday,
                'top_products' => $topProducts,
                'stock_alerts' => $stockAlerts,
                'opening_amount' => $register?->opening_amount ?? 0,
                'current_cash' => $currentCash,
                'current_bank' => $currentBank,
                'status' => $register?->status ?? 'no_register',
            ],
        ]);
    }
}
