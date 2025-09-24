<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        // Filter kasir
        if ($request->filled('cashier')) {
            $query->where('user_id', $request->cashier);
        }

        $transactions = $query->paginate(10)->withQueryString();

        // Metrics (today, week, month, all time)
        $dashboard = [
            'today' => [
                'total_transactions' => Transaction::whereDate('created_at', today())->count(),
                'total_sales' => Transaction::whereDate('created_at', today())->sum('grand_total'),
                'total_items' => Transaction::whereDate('created_at', today())->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => Transaction::whereDate('created_at', today())->avg('grand_total'),
            ],
            'week' => [
                'total_transactions' => Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'total_sales' => Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('grand_total'),
                'total_items' => Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->avg('grand_total'),
            ],
            'month' => [
                'total_transactions' => Transaction::whereMonth('created_at', now()->month)->count(),
                'total_sales' => Transaction::whereMonth('created_at', now()->month)->sum('grand_total'),
                'total_items' => Transaction::whereMonth('created_at', now()->month)->withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => Transaction::whereMonth('created_at', now()->month)->avg('grand_total'),
            ],
            'alltime' => [
                'total_transactions' => Transaction::count(),
                'total_sales' => Transaction::sum('grand_total'),
                'total_items' => Transaction::withSum('items', 'quantity')->get()->sum('items_sum_quantity'),
                'avg_transaction' => Transaction::avg('grand_total'),
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

        return Excel::download(new SalesReportExport($from, $to), 'sales-report.xlsx');
    }
}