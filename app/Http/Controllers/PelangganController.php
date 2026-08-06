<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\Menu;

class PelangganController extends Controller
{
    public function index($token)
    {
        $meja = Meja::where('token', $token)->first();

        if (!$meja) {
            return response()->view('pelanggan.mejanonaktif', [
                'pesan' => 'Meja tidak ditemukan! Pastikan Anda men-scan QR Code resmi.'
            ], 404);
        }

        if ($meja->status != 'Active') {
            return response()->view('pelanggan.mejanonaktif', [
                'pesan' => 'Meja ini sedang dinonaktifkan. Silakan pindah ke meja lain atau hubungi pelayan.'
            ], 403);
        }

        // 1. Ambil semua menu yang tersedia
        $menus = Menu::where('status_tersedia', 1)->get();

        // 2. Ambil daftar kategori yang unik/berbeda saja (Misal: Makanan, Minuman)
        // Gunakan pluck('kategori') untuk menjadikannya array biasa
        $kategoris = Menu::where('status_tersedia', 1)
                         ->select('kategori')
                         ->distinct()
                         ->pluck('kategori');

        // 3. Kirim $menus dan $kategoris ke view
        return view('pelanggan.menu', compact('meja', 'menus', 'kategoris'));
    }
    
    // Fungsi untuk menampilkan halaman keranjang pesanan
    public function pesanan($token)
    {
        // Cari data meja berdasarkan token
        $meja = \App\Models\Meja::where('token', $token)->first();

        // Jika meja tidak ada atau nonaktif, arahkan ke halaman error/meja nonaktif
        if (!$meja || $meja->status != 'Active') {
            return response()->view('pelanggan.mejanonaktif', [
                'pesan' => 'Meja tidak aktif atau tidak ditemukan.'
            ], 403);
        }

        // Tampilkan halaman pesanan dan kirimkan data meja
        return view('pelanggan.pesanan', compact('meja'));
    }
}