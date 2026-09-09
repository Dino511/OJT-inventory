<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UnitConversionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\DashboardController;

// Guest Routes (Accessible only when NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

// Authenticated Protected Routes (Requires Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/locale/{locale}', [LocaleController::class, 'set'])->name('locale.set');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/history', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/history/export', [ActivityLogController::class, 'export'])->name('activity-log.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // CRUD Resource Routes
    Route::resource('companies', CompanyController::class)->except('destroy');
    Route::patch('/companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');
    Route::get('/products/history', [ProductController::class, 'historyIndex'])->name('products.history.index');
    Route::resource('products', ProductController::class)->except('destroy');
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::get('/products/{product}/history', [ProductController::class, 'history'])->name('products.history');

    Route::middleware('role:super_admin')->group(function () {
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
    Route::resource('categories', ProductCategoryController::class);
    Route::post('/categories/{category}/subcategories', [ProductCategoryController::class, 'storeSubcategory'])->name('categories.subcategories.store');
    Route::resource('suppliers', SupplierController::class);
    Route::get('/inventory/history', [InventoryController::class, 'historyIndex'])->name('inventory.history.index');
    Route::get('/inventory/history/export', [InventoryController::class, 'exportHistoryCsv'])->name('inventory.history.export');
    Route::resource('inventory', InventoryController::class)->except('destroy');
    Route::get('/inventory/{inventory}/history', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('/inventory/{inventory}/transfer', [InventoryController::class, 'showTransfer'])->name('inventory.transfer');
    Route::post('/inventory/{inventory}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer.store');

    Route::resource('locations', LocationController::class)->except('destroy');
    Route::patch('/locations/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('locations.toggle-status');
    Route::resource('base-units', UnitOfMeasureController::class);
    Route::resource('unit-conversions', UnitConversionController::class);

    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

    Route::get('/stock-transfers/{stock_transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    Route::post('/stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::post('/stock-transfers/{stock_transfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel');

    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/export', [DeliveryController::class, 'export'])->name('deliveries.export');

    Route::middleware('role:super_admin')->group(function () {
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        Route::delete('/stock-transfers/{stock_transfer}', [StockTransferController::class, 'destroy'])->name('stock-transfers.destroy');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/deliveries/{purchase_order}/edit', [DeliveryController::class, 'edit'])->name('deliveries.edit');
        Route::put('/deliveries/{purchase_order}', [DeliveryController::class, 'update'])->name('deliveries.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Root Redirect
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });
});