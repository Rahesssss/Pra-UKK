<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Detail_Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Cek autentikasi staf admin
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        // ============================================================
        // TENTUKAN MODE FILTER: bulanan atau custom range
        // ============================================================
        $filterMode = $request->input('filter_mode', 'bulanan'); // 'bulanan' | 'custom'

        // --- Mode Bulanan ---
        // Default: bulan & tahun saat ini
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        // Validasi bulan & tahun
        $bulan = max(1, min(12, $bulan));
        $tahun = max(2020, min((int) now()->year, $tahun));

        $selectedDate  = Carbon::createFromDate($tahun, $bulan, 1);
        $start_bulanan = $selectedDate->copy()->startOfMonth()->toDateString();
        $end_bulanan   = $selectedDate->copy()->endOfMonth()->toDateString();

        // --- Mode Custom Range ---
        $start_custom = $request->input('start_date', $start_bulanan);
        $end_custom   = $request->input('end_date', $end_bulanan);

        // Tentukan tanggal aktif berdasarkan mode
        if ($filterMode === 'custom') {
            $start_date = $start_custom;
            $end_date   = $end_custom;
        } else {
            $start_date = $start_bulanan;
            $end_date   = $end_bulanan;
        }

        // ============================================================
        // QUERY PESANAN
        // ============================================================
        $pesanans = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pembayaran', 'Lunas')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Pertahankan semua query param saat pagination

        // ============================================================
        // NOMOR URUT HARIAN (konsisten dengan dashboard)
        // ============================================================
        // Kumpulkan semua tanggal unik dalam hasil query
        $tanggalUnik = $pesanans->getCollection()
            ->pluck('created_at')
            ->map(fn($d) => $d->toDateString())
            ->unique()
            ->values();

        // Buat mapping: pesanan_id => nomor_urut_hari_itu
        $nomorUrutMap = [];
        foreach ($tanggalUnik as $tgl) {
            $semuaIdHariItu = Pesanan::whereDate('created_at', $tgl)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id');

            foreach ($semuaIdHariItu as $index => $pid) {
                $nomorUrutMap[$pid] = $index + 1;
            }
        }

        // Sisipkan nomor urut ke masing-masing objek pesanan
        $pesanans->getCollection()->transform(function ($pesanan) use ($nomorUrutMap) {
            $pesanan->daily_order_number = $nomorUrutMap[$pesanan->id] ?? 0;
            return $pesanan;
        });

        // ============================================================
        // STATISTIK (hitung dari SEMUA data periode, bukan hanya halaman ini)
        // ============================================================
        $allIds = Pesanan::where('status_pembayaran', 'Lunas')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->pluck('id');

        $totalPendapatan = Pesanan::whereIn('id', $allIds)->sum('total_harga');
        $totalPesanan    = $allIds->count();

        $menuTerlaris = Detail_Pesanan::whereIn('pesanan_id', $allIds)
            ->select('menu_id', DB::raw('SUM(jumlah) as total_qty'))
            ->groupBy('menu_id')
            ->orderBy('total_qty', 'desc')
            ->with('menu')
            ->first();

        // ============================================================
        // DATA PENDUKUNG VIEW
        // ============================================================
        // List bulan untuk dropdown
        $listBulan = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];

        // List tahun: 3 tahun ke belakang hingga sekarang
        $listTahun = range(now()->year - 3, now()->year);

        // Label periode aktif untuk ditampilkan di UI
        $labelPeriode = $filterMode === 'custom'
            ? Carbon::parse($start_date)->translatedFormat('d M Y') . ' – ' . Carbon::parse($end_date)->translatedFormat('d M Y')
            : $listBulan[$bulan] . ' ' . $tahun;

        return view('admin.laporan', compact(
            'pesanans',
            'totalPendapatan',
            'totalPesanan',
            'menuTerlaris',
            'filterMode',
            'bulan',
            'tahun',
            'listBulan',
            'listTahun',
            'start_custom',
            'end_custom',
            'labelPeriode',
            'start_date',
            'end_date'
        ));
    }

    public function showDetail($id)
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        $pesanan = Pesanan::with('detailPesanan.menu', 'meja')->findOrFail($id);

        // Hitung nomor urut harian
        $semuaIdHariItu = Pesanan::whereDate('created_at', $pesanan->created_at->toDateString())
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id');

        $nomorUrutMap = [];
        foreach ($semuaIdHariItu as $index => $pid) {
            $nomorUrutMap[$pid] = $index + 1;
        }

        $pesanan->daily_order_number = $nomorUrutMap[$pesanan->id] ?? 0;

        return view('admin.laporan-detail', compact('pesanan'));
    }
}
