<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::when(
          $request->has('search') && $request->search !== '', 
          fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
        )->paginate(10);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'contact' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
            ]);

            Supplier::create($data);

            return redirect()->route('suppliers.index')->with([
                'flash' => ['success' => 'Supplier berhasil ditambahkan'],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'flash' => ['error' => $e->getMessage()],
            ]);
        }
    }

    public function edit(Request $request, Supplier $supplier)
    {
        $back = $request->query('back');
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'back' => $back
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'contact' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
            ]);

            $supplier->update($data);

            $back = $request->query('back');
            if ($back) {
                return redirect($back)->with([
                    'flash' => ['success' => 'Supplier berhasil diperbarui'],
                ]);
            }

            return redirect()->route('suppliers.index')->with([
                'flash' => ['success' => 'Supplier berhasil diperbarui'],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'flash' => ['error' => $e->getMessage()],
            ]);
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')->with([
                'flash' => ['success' => 'Supplier berhasil dihapus'],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'flash' => ['error' => $e->getMessage()],
            ]);
        }
    }
}
