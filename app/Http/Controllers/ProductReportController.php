<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductReportExport;

class ProductReportController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category','supplier'])
            ->withCount('transactionItems')
            ->when($request->search, fn($q) =>
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('sku','like','%'.$request->search.'%')
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_products'   => Product::count(),
            'active_products'  => Product::where('status','active')->count(),
            'inactive_products'=> Product::where('status','inactive')->count(),
            'low_stock'        => Product::where('stock','<=',5)->count(),
            'dead_products'    => Product::doesntHave('transactionItems')->where('stock','>',0)->count(),
        ];

        return Inertia::render('Reports/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
            'metrics' => $metrics,
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new ProductReportExport, 'product-report.xlsx');
    }
}
