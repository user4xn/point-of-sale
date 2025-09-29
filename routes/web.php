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
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\CashReportController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\StockOpnameReportController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QzSignController;
use App\Http\Controllers\ExpenditureController;

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

    Route::post('/qz-sign', [QzSignController::class, 'sign']);
    
    Route::get('/transaction/cashier', [TransactionController::class, 'cashier'])->name('transaction.cashier');
    Route::get('/transaction/today', [TransactionController::class, 'today'])->name('transaction.today');
    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/transaction/detail/{transaction}', [TransactionController::class, 'detail'])->name('transaction.detail');
    Route::get('/transaction/search-product', [TransactionController::class, 'searchProduct'])->name('transaction.search');
    Route::get('/transaction/print/{transaction}', [TransactionController::class, 'print'])->name('transaction.print');
    Route::post('/transaction/print-pos/{transaction}', [TransactionController::class, 'printDirect'])->name('transaction.print.direct');

    Route::resource('expenditures', ExpenditureController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::post('/customers', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customers/find', [CustomerController::class, 'findByPhone'])->name('customer.find');

    Route::get('/cash-register/check', [CashRegisterController::class, 'check'])->name('cash-register.check');
    Route::post('/cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::post('/cash-register/close', [CashRegisterController::class, 'close'])->name('cash-register.close');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('products', ProductController::class);
    Route::delete('products/unit/{unit}', [ProductController::class, 'destroyUnit']);
    Route::delete('products/category/{category}', [ProductController::class, 'destroyCategory']);

    Route::get('stock-opnames/', [StockOpnameController::class, 'index'])->name('stockopname.index');
    Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])->name('stockopname.create');
    Route::post('stock-opnames/', [StockOpnameController::class, 'store'])->name('stockopname.store');
    Route::get('stock-opnames/{id}', [StockOpnameController::class, 'show'])->name('stockopname.show');
    Route::post('stock-opnames/{id}/confirm', [StockOpnameController::class, 'confirm'])->name('stockopname.confirm');

    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/void', [PurchaseOrderController::class, 'void'])->name('purchase-orders.void');
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');

    Route::resource('purchase-returns', PurchaseReturnController::class);
    Route::post('purchase-returns/{purchaseReturn}/confirm', [PurchaseReturnController::class, 'confirm'])->name('purchase-returns.confirm');
    Route::post('purchase-returns/{purchaseReturn}/void', [PurchaseReturnController::class, 'void'])->name('purchase-returns.void');
    Route::get('purchase-returns/{purchaseReturn}/print', [PurchaseReturnController::class, 'print'])->name('purchase-returns.print');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function(){
            return Inertia::render('Reports/Index');
        })->name('index');

        Route::get('/sales', [SalesReportController::class, 'index'])->name('sales.index');
        Route::get('/sales/export', [SalesReportController::class, 'export'])->name('sales.export');

        Route::get('/products', [ProductReportController::class, 'index'])->name('products.index');
        Route::get('/products/export', [ProductReportController::class, 'export'])->name('products.export');

        Route::get('/cash', [CashReportController::class, 'index'])->name('cash.index');
        Route::get('/cash/export', [CashReportController::class, 'export'])->name('cash.export');

        Route::get('/purchase', [PurchaseReportController::class, 'index'])->name('purchase.index');
        Route::get('/purchase/{id}/detail', [PurchaseReportController::class, 'detail'])->name('purchase.detail');
        Route::get('/purchase/export', [PurchaseReportController::class, 'export'])->name('purchase.export');

        Route::get('/stockopname', [StockOpnameReportController::class, 'index'])->name('stockopname.index');
        Route::get('/stockopname/export', [StockOpnameReportController::class, 'export'])->name('stockopname.export');
    });
});

Route::middleware(['auth', 'verified', 'can:admin-only'])->group(function () {
  Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
  Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

  Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';
