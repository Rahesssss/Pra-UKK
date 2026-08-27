<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Menu;

class MenuController extends Controller
{
    // 1. Menampilkan Daftar Menu (beserta Search & Filter)
    public function index(Request $request)
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }
        $query = Menu::query();
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_menu', 'like', '%' . $search . '%');
        } elseif ($request->has('status') && $request->status != '' && $request->status != 'semua') {
            // Diubah mengecek string enum 'tersedia' atau 'habis'
            if ($request->status == 'tersedia') {
                $query->where('status_tersedia', 'tersedia');
            } elseif ($request->status == 'habis') {
                $query->where('status_tersedia', 'habis');
            }
        }
        $menus = $query->orderBy('id_menu', 'desc')->paginate(10)->withQueryString();
        return view('admin.menu', compact('menus'));
    }

    // 2. Menampilkan Form Tambah Menu
    public function create()
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }
        return view('admin.tambah-menu');
    }

    // 3. Menyimpan Menu Baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga' => 'required|numeric|min:1000|max:1000000',
            'kategori' => 'required|string|max:50',
            'status_tersedia' => 'required|in:tersedia,habis',
            'gambar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'harga.min' => 'Harga minimal Rp 1.000',
            'harga.max' => 'Harga maksimal Rp 1.000.000',
        ]);

        $namaFile = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $namaFile = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/menu'), $namaFile);
        }

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'status_tersedia' => $request->status_tersedia,
            'gambar' => $namaFile,
        ]);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    // 4. Menampilkan Form Edit Menu
    public function edit($id)
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        $menu = Menu::findOrFail($id);
        return view('admin.edit-menu', compact('menu'));
    }

    // 5. Menyimpan Perubahan Edit Menu
    public function update(Request $request, $id)
    {
        // Validasi, perhatikan tambahan validasi gambar
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga' => 'required|numeric|min:1000|max:1000000',
            'kategori' => 'required|string|max:50',
            'status_tersedia' => 'required|in:tersedia,habis',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'harga.min' => 'Harga minimal Rp 1.000',
            'harga.max' => 'Harga maksimal Rp 1.000.000',
        ]);

        $menu = Menu::findOrFail($id);
        $namaFileGambar = $menu->gambar;

        // Cek jika admin mengunggah gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama dari folder public/images/menu jika ada
            if ($menu->gambar && file_exists(public_path('uploads/menu/' . $menu->gambar))) {
                unlink(public_path('uploads/menu/' . $menu->gambar));
            }

            // Simpan gambar baru
            $file = $request->file('gambar');
            $namaFileGambar = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/menu'), $namaFileGambar);
        }

        // Update ke database
        $menu->update([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'status_tersedia' => $request->status_tersedia,
            'gambar' => $namaFileGambar,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Data menu berhasil diperbarui!');
    }
    // 6. Menghapus Menu
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        if ($menu->gambar && File::exists(public_path('uploads/menu/' . $menu->gambar))) {

            File::delete(public_path('uploads/menu/' . $menu->gambar));
        }

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
