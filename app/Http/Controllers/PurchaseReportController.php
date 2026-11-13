<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseReportExport;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        $orders = PurchaseOrder::with(['supplier','items.product'])
            ->whereBetween('order_date', [$from, $to])
            ->whereIn('status', ['completed', 'received'])
            ->orderBy('order_date','desc')
            ->paginate(15)
            ->withQueryString();

        $returns = PurchaseReturn::with(['supplier','items.product'])
            ->whereBetween('return_date', [$from, $to])
            ->orderBy('return_date','desc')
            ->get();

        // Supplier stats
        $supplierStats = PurchaseOrder::selectRaw('supplier_id, COUNT(*) as total_po, SUM(total) as total_amount')
            ->whereBetween('order_date', [$from, $to])
            ->whereIn('status', ['completed', 'received'])
            ->groupBy('supplier_id')
            ->with('supplier')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $metrics = [
            'total_purchase' => PurchaseOrder::whereBetween('order_date', [$from, $to])->whereIn('status', ['completed', 'received'])->sum('total'),
            'total_return'   => $returns->whereIn('status', ['completed'])->sum('total'),
            'supplier_count' => PurchaseOrder::whereBetween('order_date', [$from, $to])->whereIn('status', ['completed', 'received'])->distinct('supplier_id')->count('supplier_id'),
            'po_count'       => $orders->total(),
            'top_suppliers'  => $supplierStats,
        ];

        return Inertia::render('Reports/Purchase/Index', [
            'orders'   => $orders,
            'returns'  => $returns,
            'filters'  => compact('from','to'),
            'metrics'  => $metrics,
        ]);
    }

    public function detail($id)
    {
        $order = PurchaseOrder::with(['supplier','items.product'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'order'   => $order,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->endOfMonth()->toDateString());

        return Excel::download(new PurchaseReportExport($from, $to), "purchase-report-{$from}-{$to}.xlsx");
    }
}