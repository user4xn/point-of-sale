<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'unit', 'supplier'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $products->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($kategori = $request->get('kategori')) {
            $products->where('category_id', $kategori);
        }

        if ($supplier = $request->get('supplier')) {
            $products->where('supplier_id', $supplier);
        }

        if ($status = $request->get('status')) {
            $products->where('status', $status);
        }

        if ($request->boolean('empty_stock')) {
            $products->where('stock', '<=', 0);
        }

        $products = $products->paginate(10)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::all(),
            'units' => Unit::all(),
            'suppliers' => Supplier::all(),
            'filters' => $request->only(['search','kategori','supplier','status','empty_stock']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::all(),
            'units' => Unit::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products',
                'category_id' => 'nullable|exists:categories,id',
                'unit_id' => 'nullable|exists:units,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'purchase_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            if ($request->new_category) {
                $category = Category::create(['name' => $request->new_category]);
                $data['category_id'] = $category->id;
            }

            if ($request->new_unit) {
                $unit = Unit::create(['name' => $request->new_unit]);
                $data['unit_id'] = $unit->id;
            }

            if ($request->new_supplier) {
                $supplier = Supplier::create(['name' => $request->new_supplier]);
                $data['supplier_id'] = $supplier->id;
            }

            Product::create($data);

            return redirect()->route('products.index')->with([
                'flash' => ['success' => 'Produk berhasil ditambahkan'],
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'flash' => ['error' => 'Gagal menambahkan produk: ' . $e->getMessage()],
            ]);
        }
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product->load(['category','unit','supplier', 'unitConversions']),
            'categories' => Category::all(),
            'units' => Unit::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    { 
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
                'category_id' => 'nullable|exists:categories,id',
                'unit_id' => 'nullable|exists:units,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'purchase_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            } else {
                unset($data['image']);
            }

            if ($request->new_category) {
                $category = Category::create(['name' => $request->new_category]);
                $data['category_id'] = $category->id;
            }

            if ($request->new_unit) {
                $unit = Unit::create(['name' => $request->new_unit]);
                $data['unit_id'] = $unit->id;
            }

            if ($request->new_supplier) {
                $supplier = Supplier::create(['name' => $request->new_supplier]);
                $data['supplier_id'] = $supplier->id;
            }

            if ($request->unit_conversions) {
                $existingIds = $product->unitConversions()->pluck('id')->toArray();
                $requestIds  = collect($request->unit_conversions)->pluck('id')->filter()->toArray();

                $toDelete = array_diff($existingIds, $requestIds);
                if (!empty($toDelete)) {
                    $product->unitConversions()->whereIn('id', $toDelete)->delete();
                }

                foreach ($request->unit_conversions as $uc) {
                    if (!empty($uc['id'])) {
                        $product->unitConversions()->where('id', $uc['id'])->update([
                            'unit_name'      => $uc['unit_name'],
                            'conversion'     => $uc['conversion'],
                            'purchase_price' => $uc['purchase_price'] ?? null,
                            'sell_price'     => $uc['sell_price'] ?? null,
                        ]);
                    } else {
                        $product->unitConversions()->create([
                            'unit_name'      => $uc['unit_name'],
                            'conversion'     => $uc['conversion'],
                            'purchase_price' => $uc['purchase_price'] ?? null,
                            'sell_price'     => $uc['sell_price'] ?? null,
                        ]);
                    }
                }
            }

            $product->update($data);

            return redirect()->route('products.index')->with([
                'flash' => ['success' => 'Produk berhasil diperbarui'],
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'flash' => ['error' => 'Gagal memperbarui produk: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')->with([
                'flash' => ['success' => 'Produk berhasil dihapus'],
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'flash' => ['error' => 'Gagal menghapus produk: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroyUnit(Unit $unit)
    {
        try {
            $unit->delete();
            return redirect()->route('products.index')->with([
                'flash' => ['success' => 'Unit berhasil dihapus'],
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'flash' => ['error' => 'Gagal menghapus unit: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroyCategory(Category $category)
    {
        try {
            $category->delete();
            return redirect()->route('products.index')->with([
                'flash' => ['success' => 'Kategori berhasil dihapus'],
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'flash' => ['error' => 'Gagal menghapus kategori: ' . $e->getMessage()],
            ]);
        }
    }
}
