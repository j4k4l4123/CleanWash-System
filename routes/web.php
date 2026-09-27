<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public Status Tracking (Cek Status / Resi Cucian tanpa login)
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/tracking/{kode_order}', [TrackingController::class, 'show'])->name('tracking.show');

// Authentication (Guest Only)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (Authenticated Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Kasir & Admin Laundry)
Route::middleware('auth')->group(function (): void {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Pelanggan (Customer Management)
    Route::resource('customers', CustomerController::class)->except(['destroy']);
    Route::post('/api/customers/quick-create', [CustomerController::class, 'quickStore'])->name('customers.quick-store');

    // Layanan (Lihat Katalog untuk Kasir & Admin)
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

    // Order Cucian
    Route::resource('orders', OrderController::class)->except(['destroy']);
    Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');

    // Pembayaran
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/orders/{order}/pay', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    // Khusus Admin / Owner (Role: Admin)
    Route::middleware('role:admin')->group(function (): void {
        // Kelola Petugas / Pengguna
        Route::resource('users', UserController::class)->except(['show']);

        // Kelola Tarif & Tambah Layanan
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');

        // Tindakan Penghapusan Transaksi & Pelanggan
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });
});
