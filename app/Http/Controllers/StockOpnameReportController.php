<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameReportExport;

class StockOpnameReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        $opnames = StockOpname::with(['user','items.product'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at','desc')
            ->paginate(15)
            ->withQueryString();

        // Metrics
        $totalOpname = StockOpname::whereBetween('created_at', [$from, $to])->count();
        $totalItems  = StockOpnameItem::whereHas('opname', fn($q) => $q->whereBetween('created_at', [$from, $to]))->count();
        $totalLoss   = StockOpnameItem::whereHas('opname', fn($q) => $q->whereBetween('created_at', [$from, $to]))
                        ->sum('difference');
        $totalNote   = StockOpnameItem::whereHas('opname', fn($q) => $q->whereBetween('created_at', [$from, $to]))
                        ->whereNotNull('note')->count();

        return Inertia::render('Reports/StockOpname/Index', [
            'filters' => compact('from','to'),
            'metrics' => [
                'total_opname' => $totalOpname,
                'total_items'  => $totalItems,
                'total_loss'   => $totalLoss,
                'total_note'   => $totalNote,
            ],
            'opnames' => $opnames,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        return Excel::download(new StockOpnameReportExport($from, $to), "stock-opname-report-{$from}-{$to}.xlsx");
    }
}
