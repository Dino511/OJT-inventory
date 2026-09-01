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
    Route::resource('locations', LocationController::class);
    Route::resource('base-units', UnitOfMeasureController::class);
    Route::resource('unit-conversions', UnitConversionController::class);

    // Root Redirect
    Route::get('/', function () {
        return redirect()->route('products.index');
    });
});