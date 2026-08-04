<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;
use Illuminate\Support\Str; // Wajib dipanggil untuk membuat Token Acak

class MejaController extends Controller
{
    // 1. Menampilkan Halaman Daftar Meja
    public function index()
    {
        // Ambil semua data meja, urutkan dari yang terbaru
        $mejas = Meja::orderBy('id_meja', 'desc')->get();
        return view('admin.meja', compact('mejas'));
    }

    // 2. Menyimpan Meja Baru ke Database MySQL
    public function store(Request $request)
    {
        $request->validate([
            'nama_meja' => 'required|string|max:50',
            'status' => 'required|in:Active,Nonaktif',
        ]);

        Meja::create([
            'nama_meja' => $request->nama_meja,
            'status' => $request->status,
            'token' => Str::random(8), // Generate otomatis 8 huruf/angka acak (Contoh: xK9aLp2z)
        ]);

        return redirect()->back()->with('success', 'Meja baru berhasil ditambahkan!');
    }

    // 3. Menghapus Data Meja dari Database MySQL
    public function destroy($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->delete();

        return redirect()->back()->with('success', 'Meja berhasil dihapus permanen!');
    }
    // 4. Mengubah Data Meja (Nama / Status Active/Nonaktif)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_meja' => 'required|string|max:50',
            'status' => 'required|in:Active,Nonaktif',
        ]);

        $meja = Meja::findOrFail($id);
        $meja->update([
            'nama_meja' => $request->nama_meja,
            'status' => $request->status,
            // Token sengaja tidak diubah agar QR code yang sudah dicetak tidak rusak, 
            // kecuali jika admin ingin meresetnya.
        ]);

        return redirect()->back()->with('success', 'Data meja berhasil diperbarui!');
    }
}