<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CashReportExport;

class CashReportController extends Controller
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
           $r->total_amount_noncash = $r->bankTransactions->sum(fn($t) => $t->amount);
           $r->total_sales_noncash = count($r->bankTransactions);
           $r->total_all_amount = $r->total_amount + $r->total_amount_noncash;

           $r->selisih_amount = $r->closing_amount - ($r->opening_amount + $r->total_amount);
        }

        // Metrics
        $all = CashRegister::whereBetween('opened_at', [$from, $to])->get();

        $metrics = [
            'total_selisih' => $all->filter(fn($r) => $r->closing_amount !== null && $r->closing_amount != $r->total_amount)->count(),
            'amount_selisih' => $all->sum(fn($r) => abs(($r->closing_amount ?? 0) - (($r->opening_amount ?? 0) + ($r->total_amount ?? 0)))),
            'selisih_bulan' => $all->whereBetween('opened_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum(fn($r) => abs(($r->closing_amount ?? 0) - (($r->opening_amount ?? 0) + ($r->total_amount ?? 0)))),
            'late_close' => $all->filter(fn($r) =>
                $r->status === 'closed' &&
                $r->closed_at &&
                (date('Y-m-d', strtotime($r->closed_at)) != date('Y-m-d', strtotime($r->opened_at)))
            )->count(),
        ];

        return Inertia::render('Reports/Cash/Index', [
            'registers' => $registers,
            'filters' => ['from' => $from, 'to' => $to],
            'metrics' => $metrics,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        return Excel::download(new CashReportExport($from, $to), 'cash-report.xlsx');
    }
}
