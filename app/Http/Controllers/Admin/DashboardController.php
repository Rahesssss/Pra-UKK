<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        // Ensure the user is authenticated as staff
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        // Eager load related models to prevent N+1 queries
        // Tampilkan pesanan 'Menunggu', 'Sedang Dimasak', dan 'Selesai' (jika waktu update kurang dari 3 menit yang lalu)
        $pesanans = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where(function($query) {
                $query->whereIn('status_pesanan', ['Menunggu', 'Sedang Dimasak'])
                      ->orWhere(function($q) {
                          $q->where('status_pesanan', 'Selesai')
                            ->where('updated_at', '>=', now()->subMinutes(3));
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('pesanans'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => 'required|in:Menunggu,Sedang Dimasak,Selesai',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->status_pesanan = $request->status_pesanan;
        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diubah!');
    }
}
