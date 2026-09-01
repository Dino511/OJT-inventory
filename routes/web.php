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

// Guest Routes (Accessible only when NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Protected Routes (Requires Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // CRUD Resource Routes
    Route::resource('companies', CompanyController::class);
    Route::resource('products', ProductController::class);
    Route::resource('categories', ProductCategoryController::class);
    Route::resource('inventory', InventoryController::class);
    Route::get('/inventory/{inventory}/transfer', [InventoryController::class, 'showTransfer'])->name('inventory.transfer');
    Route::post('/inventory/{inventory}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer.store');
    Route::resource('locations', LocationController::class);
    Route::resource('base-units', UnitOfMeasureController::class);
    Route::resource('unit-conversions', UnitConversionController::class);
    Route::resource('users', UserController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Root Redirect
    Route::get('/', function () {
        return redirect()->route('products.index');
    });
});