<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/citas');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/citas', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/citas/nueva', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/citas', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/servicios', [ServiceController::class, 'index'])->name('services');
        Route::post('/servicios', [ServiceController::class, 'store'])->name('services.store');
        Route::delete('/servicios/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::get('/citas', [ServiceController::class, 'appointments'])->name('appointments');
        Route::patch('/citas/{appointment}', [ServiceController::class, 'updateStatus'])->name('appointments.status');
    });
});
