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
        $pesanans = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pembayaran', 'Lunas')
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
