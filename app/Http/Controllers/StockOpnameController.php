<?php 

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with('user')->latest();
        if ($request->search) {
            $query->where('note', 'like', "%{$request->search}%");
        }
        $opnames = $query->paginate(10)->withQueryString();

        $lastOpname = StockOpname::latest('created_at')->first();
        $opnameCountMonth = StockOpname::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalProducts = Product::count();
        $opnameThisMonth = StockOpnameItem::whereHas('opname', function($q) {
            $q->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
        })->distinct('product_id')->count();

        $notOpnameThisMonth = $totalProducts - $opnameThisMonth;

        $neverOpname = Product::whereDoesntHave('stockOpnameItems')->count();

        $diffThisMonth = StockOpnameItem::whereHas('opname', function($q) {
            $q->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
        })->sum('difference');

        $metrics = [
            'last_opname' => $lastOpname?->created_at,
            'opname_count_month' => $opnameCountMonth,
            'not_opname_this_month' => $notOpnameThisMonth,
            'never_opname' => $neverOpname,
            'diff_this_month' => $diffThisMonth,
        ];

        return inertia('StockOpname/Index', [
            'opnames' => $opnames,
            'metrics' => $metrics,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $product = Product::all();
        return inertia('StockOpname/Create', [
          'products' => $product
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,partial',
            'note' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|integer|min:0',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'user_id' => auth()->id(),
                'type' => $request->type,
                'note' => $request->note,
                'status' => 'draft',
            ]);

            foreach ($request->items as $i) {
                $product = Product::find($i['product_id']);

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'system_qty' => $product->stock,
                    'physical_qty' => $i['physical_qty'],
                    'difference' => $i['physical_qty'] - $product->stock,
                    'note' => $i['note'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('stockopname.index')->with([
              'flash' => ['success' => 'sukses menyimpan opname'],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with([
              'flash' => ['error' => 'gagal menyimpan opname: ' . $e->getMessage()],
            ]);
        }
    }

    public function show($id)
    {
        $opname = StockOpname::with(['items.product', 'user'])->findOrFail($id);

        return inertia('StockOpname/Show', [
            'opname' => $opname,
        ]);
    }

    public function confirm($id)
    {
        DB::beginTransaction();
        try {
            $opname = StockOpname::with('items.product')->findOrFail($id);

            if ($opname->status === 'confirmed') {
                return back()->with([
                    'flash' => ['error' => 'opname sudah dikonfirmasi'],
                ]);
            }

            foreach ($opname->items as $item) {
                $item->product->update([
                    'stock' => $item->physical_qty, // update stok sesuai hasil opname
                ]);
            }

            $opname->update(['status' => 'confirmed']);

            DB::commit();
            return redirect()->route('stockopname.show', $id)->with([
              'flash' => ['success' => 'sukses konfirmasi opname'],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with([
              'flash' => ['error' => 'gagal konfirmasi opname: ' . $e->getMessage()],
            ]);
        }
    }
}
