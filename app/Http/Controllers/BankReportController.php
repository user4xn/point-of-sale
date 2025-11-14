<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        $registers = CashRegister::with(['user', 'bankTransactions'])
            ->whereBetween('opened_at', [$from, $to])
            ->orderByDesc('opened_at')
            ->paginate(10)
            ->withQueryString();

        foreach ($registers as $r) {
            $r->total_amount_noncash = $r->bankTransactions->sum('amount');
            $r->total_sales_noncash  = $r->bankTransactions->count();
        }

        // Metrics
        $all = CashRegister::with('bankTransactions')
            ->whereBetween('opened_at', [$from, $to])
            ->get();

        $metrics = [
            'total_transaksi_bank' => $all->sum(fn($r) => $r->bankTransactions->count()),
            'total_amount_bank'    => $all->sum(fn($r) => $r->bankTransactions->sum('amount')),
            'register_ada_transaksi' => $all->filter(fn($r) => $r->bankTransactions->count() > 0)->count(),
        ];

        return Inertia::render('Reports/Bank/Index', [
            'registers' => $registers,
            'filters'   => ['from' => $from, 'to' => $to],
            'metrics'   => $metrics,
        ]);
    }
}
