<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Authentication (Guest Only)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (Authenticated Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Kasir & Admin Laundry)
Route::middleware('auth')->group(function (): void {
    // Redirection / Default Route to Orders
    Route::get('/', [OrderController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [OrderController::class, 'index']);

    // Pelanggan (Customer Management)
    Route::resource('customers', CustomerController::class);
    Route::post('/api/customers/quick-create', [CustomerController::class, 'quickStore'])->name('customers.quick-store');

    // Order Cucian
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
});
