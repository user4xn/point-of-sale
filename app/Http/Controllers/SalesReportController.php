<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items.product'])
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [Carbon::parse($request->from)->startOfDay(), Carbon::parse($request->to)->endOfDay()]);
        }

        // Filter kasir
        if ($request->filled('cashier')) {
            $query->where('user_id', $request->cashier);
        }

        $transactions = $query->paginate(10)->withQueryString();

        $metricQuery = fn () => Transaction::where('status', 'paid')
            ->when($request->filled('from') && $request->filled('to'), fn ($q) => $q->whereBetween('created_at', [Carbon::parse($request->from)->startOfDay(), Carbon::parse($request->to)->endOfDay()]))
            ->when($request->filled('cashier'), fn ($q) => $q->where('user_id', $request->cashier));

        // Metrics (today, week, month, all time)
        $dashboard = [
            'today' => [
                'total_transactions' => (clone $metricQuery())->whereDate('created_at', today())->count(),
                'total_sales' => (clone $metricQuery())->whereDate('created_at', today())->sum('grand_total'),
                'total_items' => (clone $metricQuery())->whereDate('created_at', today())->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => (clone $metricQuery())->whereDate('created_at', today())->avg('grand_total'),
            ],
            'week' => [
                'total_transactions' => $metricQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'total_sales' => $metricQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('grand_total'),
                'total_items' => $metricQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => $metricQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->avg('grand_total'),
            ],
            'month' => [
                'total_transactions' => $metricQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'total_sales' => $metricQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('grand_total'),
                'total_items' => $metricQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => $metricQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->avg('grand_total'),
            ],
            'alltime' => [
                'total_transactions' => $metricQuery()->count(),
                'total_sales' => $metricQuery()->sum('grand_total'),
                'total_items' => $metricQuery()->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => $metricQuery()->avg('grand_total'),
            ],
        ];

        return Inertia::render('Reports/Sales/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['from','to','cashier']),
            'dashboard' => $dashboard,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        return Excel::download(new SalesReportExport($from, $to, $request->get('cashier')), 'sales-report.xlsx');
    }
}