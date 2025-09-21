<?php 

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with('supplier')->orderBy('created_at', 'desc');

        if ($request->supplier_id) {
            $orders->where('supplier_id', $request->supplier_id);
        }
        if ($request->status) {
            $orders->where('status', $request->status);
        }

        $orders = $orders->paginate(10)->withQueryString();

        // 🔹 Hitung metrics
        $metrics = [
            'total'     => PurchaseOrder::count(),
            'draft'     => PurchaseOrder::where('status', 'draft')->count(),
            'completed' => PurchaseOrder::where('status', 'completed')->count(),
            'void'      => PurchaseOrder::where('status', 'void')->count(),
            'in_progress' => PurchaseOrder::where('status', 'in_progress')->count(), // kalau ada
        ];

        return Inertia::render('PurchaseOrder/Index', [
            'orders'    => $orders,
            'suppliers' => Supplier::all(),
            'filters'   => $request->only(['supplier_id','status']),
            'metrics'   => $metrics,
        ]);
    }


    public function create()
    {
        return Inertia::render('PurchaseOrder/Create', [
            'suppliers' => Supplier::all(),
            'products' => Product::with('unit', 'unitConversions')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_conversion_id' => 'nullable|exists:product_unit_conversions,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => 'PO-' . now()->format('YmdHis'),
                'order_date' => $data['order_date'],
                'total' => 0,
                'status' => 'draft',
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'unit_conversion_id' => $item['unit_conversion_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $po->update(['total' => $total]);

            DB::commit();

            return redirect()->route('purchase-orders.index')
                ->with('flash', ['success' => 'Purchase Order berhasil dibuat']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('flash', ['error' => 'Gagal menyimpan PO: '.$e->getMessage()]);
        }
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('PurchaseOrder/Edit', [
            'order' => $purchaseOrder->load(['items.product.unit','items.product.unitConversions','supplier']),
            'suppliers' => Supplier::all(),
            'products' => Product::with(['unit','unitConversions'])->get(),
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:draft,completed,void',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_conversion_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        // kalau draft → simpan qty & produk, harga biarin
        if ($request->action !== 'complete') {
            foreach ($data['items'] as $item) {
                $poItem = PurchaseOrderItem::find($item['id']);
                $poItem->update([
                    'quantity' => $item['quantity'],
                    'unit_conversion_id' => $item['unit_conversion_id'],
                ]);
            }
            return back()->with('flash', ['success' => 'Draft PO berhasil diperbarui']);
        }

        // kalau complete → isi harga, hitung total, update stok
        $total = 0;
        foreach ($data['items'] as $item) {
            $poItem = PurchaseOrderItem::find($item['id']);
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;

            $poItem->update([
                'price' => $item['price'],
                'subtotal' => $subtotal,
            ]);

            // update stok produk
            $product = $poItem->product;
            $qtyToAdd = $item['quantity'];

            if ($item['unit_conversion_id']) {
                $conversion = $poItem->unitConversion?->conversion ?? 1;
                $qtyToAdd *= $conversion;
            }

            $product->increment('stock', $qtyToAdd);
        }

        $purchaseOrder->update([
            'total' => $total,
            'status' => 'completed',
        ]);

        return back()->with('flash', ['success' => 'PO berhasil diselesaikan & stok ditambahkan']);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('flash', ['success' => 'PO berhasil dihapus']);
    }

    public function void(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'completed') {
            return back()->with('flash', ['error' => 'Hanya PO completed yang bisa di-void']);
        }

        // rollback stok
        foreach ($purchaseOrder->items as $item) {
            $qty = $item->quantity;
            if ($item->unit_conversion_id) {
                $qty *= $item->unitConversion?->conversion ?? 1;
            }
            $item->product->decrement('stock', $qty);
        }

        $purchaseOrder->update(['status' => 'void']);

        return redirect()->route('purchase-orders.index')->with('flash', ['success' => 'PO berhasil di-void & stok dikembalikan']);
    }
}
