<?php

namespace App\Http\Controllers;

use App\Models\Expenditure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenditureController extends Controller
{
    public function index(Request $request)
    {
        $query = Expenditure::with('user')->latest();

        if ($request->search) {
            $query->where('note', 'like', "%{$request->search}%");
        }

        $expenditures = $query->paginate(10)->withQueryString();

        // Metrics
        $lastExpenditure = Expenditure::latest('date')->first();
        $totalThisMonth = Expenditure::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        $totalThisYear = Expenditure::whereYear('date', now()->year)->sum('amount');

        $metrics = [
            'last_expenditure' => $lastExpenditure?->date,
            'total_this_month' => $totalThisMonth,
            'total_this_year'  => $totalThisYear,
        ];

        return inertia('Expenditure/Index', [
            'expenditures' => $expenditures,
            'metrics' => $metrics,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return inertia('Expenditure/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            Expenditure::create([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'amount' => $request->amount,
                'note' => $request->note,
            ]);

            DB::commit();
            return redirect()->route('expenditures.index')->with([
                'flash' => ['success' => 'Pengeluaran berhasil disimpan'],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with([
                'flash' => ['error' => 'Gagal menyimpan pengeluaran: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroy($id)
    {
        $expenditure = Expenditure::findOrFail($id);

        $expenditure->delete();

        return back()->with([
            'flash' => ['success' => 'Pengeluaran berhasil dihapus'],
        ]);
    }
}
