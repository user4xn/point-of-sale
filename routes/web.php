<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\TransactionController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/print/test', [PrintController::class, 'test'])->name('print.test');
    
    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');

    Route::get('/cash-register/check', [CashRegisterController::class, 'check'])->name('cash-register.check');
    Route::post('/cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::post('/cash-register/close', [CashRegisterController::class, 'close'])->name('cash-register.close');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('products', ProductController::class);
});

Route::middleware(['auth', 'verified', 'can:admin-only'])->group(function () {
  Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
  Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

  Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';
