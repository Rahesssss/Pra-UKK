<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\OrderController;

// Login Admin
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard Admin
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::post('/admin/dashboard/status/{id}', [DashboardController::class, 'updateStatus'])->name('admin.dashboard.status');

// Admin Menu
Route::get('/admin/menu', [MenuController::class, 'index'])->name('admin.menu.index');
Route::get('/admin/menu/tambah', [MenuController::class, 'create'])->name('admin.menu.create');
Route::post('/admin/menu/tambah', [MenuController::class, 'store'])->name('admin.menu.store');
Route::get('/admin/menu/{id}/edit', [MenuController::class, 'edit'])->name('admin.menu.edit');
Route::put('/admin/menu/{id}', [MenuController::class, 'update'])->name('admin.menu.update');
Route::delete('/admin/menu/{id}', [MenuController::class, 'destroy'])->name('admin.menu.destroy');

// Admin Meja
Route::get('/admin/meja', [MejaController::class, 'index'])->name('admin.meja.index');
Route::post('/admin/meja', [MejaController::class, 'store'])->name('admin.meja.store');
Route::delete('/admin/meja/{id}', [MejaController::class, 'destroy'])->name('admin.meja.destroy');
Route::put('/admin/meja/{id}', [MejaController::class, 'update'])->name('admin.meja.update');

// Admin Laporan
Route::get('/admin/laporan', [LaporanController::class, 'index'])->name('admin.laporan');
Route::get('/admin/laporan/{id}', [LaporanController::class, 'showDetail'])->name('admin.laporan.detail');

// Pelanggan
Route::get('/menu/{token}', [PelangganController::class, 'index'])->name('pelanggan.menu');
Route::get('/menu/{token}/pesanan', [PelangganController::class, 'pesanan'])->name('pelanggan.pesanan');
Route::get('/menu/{token}/histori', [OrderController::class, 'histori'])->name('pelanggan.histori');

// Order & Pembayaran
Route::post('/pesanan/store', [OrderController::class, 'store'])->name('pesanan.store');
Route::get('/pesanan-sukses/{token}', [OrderController::class, 'sukses'])->name('pesanan.sukses');

// Webhook Midtrans (satu saja)
Route::post('/midtrans-callback', [OrderController::class, 'callback'])->name('midtrans.callback');
