<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Detail_Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan pendapatan dan daftar pesanan lunas.
     */
    public function index(Request $request)
    {
        // Cek autentikasi staf admin
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        // Ambil data pesanan yang status pembayarannya lunas
        $query = Pesanan::where('status_pembayaran', 'Lunas');

        // Filter berdasarkan tanggal mulai
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        // Filter berdasarkan tanggal akhir
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $pesanans = $query->orderBy('created_at', 'desc')->paginate(10);

        // Hitung total pendapatan dan jumlah pesanan
        $totalPendapatan = $pesanans->sum('total_harga');
        $totalPesanan = $pesanans->count();

        // Cari menu terlaris berdasarkan total jumlah yang terjual
        $menuTerlaris = Detail_Pesanan::whereIn('pesanan_id', $pesanans->pluck('id'))
            ->select('menu_id', DB::raw('SUM(jumlah) as total_qty'))
            ->groupBy('menu_id')
            ->orderBy('total_qty', 'desc')
            ->with('menu')
            ->first();

        return view('admin.laporan', compact('pesanans', 'totalPendapatan', 'totalPesanan', 'menuTerlaris'));
    }

    /**
     * Menampilkan halaman detail pesanan dari laporan.
     */
    public function showDetail($id)
    {
        // Cek autentikasi staf admin
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        // Ambil pesanan beserta relasi detail, menu, dan meja
        $pesanan = Pesanan::with('detailPesanan.menu', 'meja')->findOrFail($id);

        return view('admin.laporan-detail', compact('pesanan'));
    }
}
