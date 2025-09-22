<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with('supplier')->latest();

        if ($request->supplier_id) {
            $returns->where('supplier_id', $request->supplier_id);
        }
        if ($request->status) {
            $returns->where('status', $request->status);
        }
        if ($request->search) {
            $returns->where('return_number', 'like', '%'.$request->search.'%');
        }

        $returns = $returns->paginate(10)->withQueryString();

        $metrics = [
            'total'     => PurchaseReturn::count(),
            'draft'     => PurchaseReturn::where('status', 'draft')->count(),
            'confirmed' => PurchaseReturn::where('status', 'confirmed')->count(),
            'void'      => PurchaseReturn::where('status', 'void')->count(),
        ];

        return Inertia::render('PurchaseReturn/Index', [
            'returns'   => $returns,
            'suppliers' => Supplier::all(),
            'filters'   => $request->only(['supplier_id','status','search']),
            'metrics'   => $metrics,
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseReturn/Create', [
            'suppliers' => Supplier::all(),
            'products'  => Product::with('unit','unitConversions')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason'      => 'nullable|string|max:255',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_conversion_id' => 'nullable|exists:product_unit_conversions,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
            'items.*.note'     => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $return = PurchaseReturn::create([
                'supplier_id'  => $data['supplier_id'],
                'return_number'=> 'PR-' . now()->format('YmdHis'),
                'return_date'  => $data['return_date'],
                'reason'       => $data['reason'] ?? null,
                'total'        => 0,
                'status'       => 'draft',
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'unit_conversion_id' => $item['unit_conversion_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                    'subtotal' => $subtotal,
                    'note'     => $item['note'] ?? null,
                ]);
            }

            $return->update(['total' => $total]);

            DB::commit();
            return redirect()->route('purchase-returns.index')
                ->with('flash',['success'=>'Purchase Return berhasil dibuat']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('flash',['error'=>'Gagal: '.$e->getMessage()]);
        }
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        return Inertia::render('PurchaseReturn/Edit', [
            'retur'     => $purchaseReturn->load(['items.product.unit','items.product.unitConversions','supplier']),
            'suppliers' => Supplier::all(),
            'products'  => Product::with(['unit','unitConversions'])->get(),
        ]);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return back()->with('flash', ['error' => 'Hanya retur draft yang bisa diperbarui']);
        }

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason'      => 'nullable|string|max:255',
            'items'       => 'required|array|min:1',
            'items.*.id'  => 'required|exists:purchase_return_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price'    => 'nullable|numeric|min:0',
            'items.*.note'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $purchaseReturn) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $prItem = PurchaseReturnItem::find($item['id']);
                $subtotal = ($item['price'] ?? 0) * $item['quantity'];
                $total += $subtotal;

                $prItem->update([
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'] ?? 0,
                    'subtotal' => $subtotal,
                    'note'     => $item['note'] ?? null,
                ]);
            }

            $purchaseReturn->update([
                'supplier_id' => $data['supplier_id'],
                'return_date' => $data['return_date'],
                'reason'      => $data['reason'],
                'total'       => $total,
            ]);
        });

        return back()->with('flash', ['success' => 'Retur berhasil diperbarui']);
    }

    public function confirm(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return back()->with('flash',['error'=>'Hanya retur draft yang bisa dikonfirmasi']);
        }

        DB::transaction(function () use ($purchaseReturn) {
            foreach ($purchaseReturn->items as $item) {
                $product = $item->product;
                $qty = $item->quantity;

                if ($item->unit_conversion_id) {
                    $conversion = $item->unitConversion?->conversion ?? 1;
                    $qty *= $conversion;
                }

                $product->decrement('stock', $qty);
            }

            $purchaseReturn->update(['status'=>'completed']);
        });

        return redirect()->route('purchase-returns.index')->with('flash',['success'=>'Retur berhasil dikonfirmasi & stok dikurangi']);
    }

    public function void(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'completed') {
            return back()->with('flash',['error'=>'Hanya retur confirmed yang bisa di-void']);
        }

        DB::transaction(function () use ($purchaseReturn) {
            foreach ($purchaseReturn->items as $item) {
                $qty = $item->quantity;
                if ($item->unit_conversion_id) {
                    $qty *= $item->unitConversion?->conversion ?? 1;
                }
                $item->product->increment('stock', $qty);
            }

            $purchaseReturn->update(['status'=>'void']);
        });

        return redirect()->route('purchase-returns.index')->with('flash',['success'=>'Retur berhasil dibatalkan & stok dikembalikan']);
    }

    public function print(PurchaseReturn $purchaseReturn)
    {
        $setting = Setting::first();

        $pdf = Pdf::loadView('pdf.purchase_return', [
            'retur'   => $purchaseReturn->load(['items.product.unit','items.unitConversion','supplier']),
            'setting' => $setting,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("PurchaseReturn-{$purchaseReturn->return_number}.pdf");
    }
}
