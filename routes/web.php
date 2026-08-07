<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\PelangganController;

// 1. Saat membuka http://127.0.0.1:8000/, langsung masuk ke halaman login
Route::get('/', [LoginController::class, 'showLoginForm']);

// 2. Rute untuk proses Login Admin
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');

// 3. Rute untuk Logout Admin
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('logout');

// 4. Rute Dashboard Admin
Route::get('/admin/dashboard', function () {
    if (!session()->has('staf_id')) {
        return redirect('/admin/login')->withErrors(['username' => 'Silakan login terlebih dahulu!']);
    }
    return view('admin.dashboard');
})->name('admin.dashboard');

// Admin Menu Routes
Route::get('/admin/menu', [MenuController::class, 'index'])->name('admin.menu.index');
Route::get('/admin/menu/tambah', [MenuController::class, 'create'])->name('admin.menu.create');
Route::post('/admin/menu/tambah', [MenuController::class, 'store'])->name('admin.menu.store');
Route::get('/admin/menu/{id}/edit', [MenuController::class, 'edit'])->name('admin.menu.edit');
Route::put('/admin/menu/{id}', [MenuController::class, 'update'])->name('admin.menu.update');
Route::delete('/admin/menu/{id}', [MenuController::class, 'destroy'])->name('admin.menu.destroy');

// Admin Meja Routes (SUDAH DIBERSIHKAN, HANYA ADA 1 RUTE YANG BENAR)
Route::get('/admin/meja', [MejaController::class, 'index'])->name('admin.meja.index');
Route::post('/admin/meja', [MejaController::class, 'store'])->name('admin.meja.store');
Route::delete('/admin/meja/{id}', [MejaController::class, 'destroy'])->name('admin.meja.destroy');
Route::put('/admin/meja/{id}', [MejaController::class, 'update'])->name('admin.meja.update');

// Halaman menu pelanggan berdasarkan token meja
Route::get('/menu/{token}', [PelangganController::class, 'index'])->name('pelanggan.menu');

// Rute untuk halaman pesanan pelanggan berdasarkan token meja
Route::get('/menu/{token}/pesanan', [PelangganController::class, 'pesanan'])->name('pelanggan.pesanan');

use App\Http\Controllers\OrderController;

// Route untuk menerima data keranjang dari Javascript dan mengirimkan Token Midtrans
Route::post('/pelanggan/checkout', [OrderController::class, 'checkout'])->name('pelanggan.checkout');

// Route untuk menampilkan halaman sukses setelah pelanggan selesai bayar
Route::get('/pesanan-sukses', function () {
    return "<h1>Pembayaran Berhasil! Pesanan Anda sedang diproses.</h1>"; // Nanti bisa diganti dengan tampilan Blade yang bagus
})->name('pelanggan.sukses');