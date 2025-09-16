<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
   public function index()
    {
        $today = Carbon::today();
        $transactionsToday = Transaction::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->get();

        $register = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return Inertia::render('Dashboard', [
            'dashboard' => [
                'total_transactions' => $transactionsToday->count(),
                'total_sales' => $transactionsToday->sum('grand_total'),
                'opening_amount' => $register?->opening_amount ?? 0,
                'current_cash' => $register
                    ? $register->opening_amount + $register->total_sales
                    : 0,
                'status' => $register?->status ?? 'no_register',
            ],
        ]);
    } 
}
