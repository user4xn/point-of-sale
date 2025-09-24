<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
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

        $registers = CashRegister::with(['user','registerTransactions.transaction'])
            ->whereDate('opened_at', '>=', $from)
            ->whereDate('opened_at', '<=', $to)
            ->orderBy('opened_at','desc')
            ->paginate(15)
            ->withQueryString();

        // Metrics
        $metrics = [
            'opening_cash' => CashRegister::whereBetween('opened_at', [$from, $to])->sum('opening_amount'),
            'closing_cash' => CashRegister::whereBetween('closed_at', [$from, $to])->sum('closing_amount'),
            'current_cash' => CashRegister::where('status','open')->sum('total_amount'),
            'total_transactions' => CashRegisterTransaction::whereHas('transaction', function($q) use ($from,$to) {
                $q->whereBetween('created_at', [$from,$to]);
            })->count(),
        ];

        return Inertia::render('Reports/Cash/Index', [
            'registers' => $registers,
            'filters'   => compact('from','to'),
            'metrics'   => $metrics,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        return Excel::download(new CashReportExport($from,$to), 'cash-report.xlsx');
    }
}