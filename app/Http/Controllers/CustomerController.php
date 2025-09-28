<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->withCount('transactions') // asumsi relasi di model Customer: hasMany(Transaction)
            ->withSum('transactions', 'grand_total')
            ->orderBy('name');

        $customers = $query->paginate(10)->withQueryString();

        // Metrics
        $metrics = [
            'total_customers' => Customer::count(),
            'total_points'    => Customer::sum('points'),
            'total_spent'     => Transaction::sum('grand_total'),
            'total_trx'       => Transaction::count(),
        ];

        // Top 3 customers by spending
        return $topCustomers = Customer::select('id', 'name', 'phone')
            ->with('transactions')
            ->take(3)
            ->get();

        return Inertia::render('Customers/Index', [
            'customers'    => $customers,
            'filters'      => $request->only('search'),
            'metrics'      => $metrics,
            'topCustomers' => $topCustomers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($data);

        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name,
        ]);
    }

    public function findByPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name,
            'points' => $customer->points,
        ]);
    }
}