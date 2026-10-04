<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', [InventoryController::class, 'dashboard'])->name('dashboard');
    Route::get('/productos', [InventoryController::class, 'products'])->name('products');
    Route::get('/ventas', [InventoryController::class, 'sales'])->name('sales');
    Route::post('/ventas', [InventoryController::class, 'storeSale'])->name('sales.store');
    Route::get('/movimientos', [InventoryController::class, 'movements'])->name('movements');
    Route::middleware('admin')->group(function () {
        Route::post('/productos', [InventoryController::class, 'storeProduct'])->name('products.store');
        Route::post('/categorias', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::post('/movimientos', [InventoryController::class, 'storeMovement'])->name('movements.store');
    });
});
