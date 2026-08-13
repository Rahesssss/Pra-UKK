<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ensure the user is authenticated as staff
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        // Ambil tanggal dari request, default ke hari ini
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        // Validasi format tanggal
        try {
            $selectedDate = Carbon::parse($tanggal);
        } catch (\Exception $e) {
            $selectedDate = Carbon::today();
            $tanggal = $selectedDate->toDateString();
        }

        // Query pesanan berdasarkan tanggal yang dipilih
        $pesanans = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where(function($query) use ($selectedDate) {
                $query->whereIn('status_pesanan', ['Menunggu', 'Sedang Dimasak'])
                      ->orWhere(function($q) use ($selectedDate) {
                          $q->where('status_pesanan', 'Selesai');
                      });
            })
            ->whereDate('created_at', $selectedDate->toDateString())
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil semua pesanan di tanggal tersebut untuk menentukan urutan
        $semuaPesananHariIni = Pesanan::whereDate('created_at', $selectedDate->toDateString())
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id');

        // Buat mapping: pesanan_id => nomor_urut_hari_ini
        $nomorUrut = [];
        foreach ($semuaPesananHariIni as $index => $pesananId) {
            $nomorUrut[$pesananId] = $index + 1;
        }

        $isToday = $selectedDate->isToday();
        $tanggalLabel = $this->getTanggalLabel($selectedDate);

        return view('admin.dashboard', compact(
            'pesanans', 
            'nomorUrut', 
            'tanggal', 
            'isToday',
            'tanggalLabel'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => 'required|in:Menunggu,Sedang Dimasak,Selesai',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->status_pesanan = $request->status_pesanan;
        $pesanan->save();

        // Redirect kembali dengan mempertahankan parameter tanggal
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        
        return redirect()
            ->route('admin.dashboard', ['tanggal' => $tanggal])
            ->with('success', 'Status pesanan berhasil diubah!');
    }

    //Helper Tanggal
    private function getTanggalLabel(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Hari Ini';
        }

        if ($date->isYesterday()) {
            return 'Kemarin';
        }

        return $date->translatedFormat('d M Y');
    }
}
