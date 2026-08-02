<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }

        $query = Menu::query();

        // 1. Logika Pencarian (Search)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_menu', 'like', '%' . $search . '%');
        }
        // 2. Logika Filter Status (Semua, Tersedia, Habis)
        elseif ($request->has('status') && $request->status != '' && $request->status != 'semua') {
            if ($request->status == 'tersedia') {
                $query->where('status_tersedia', 1);
            } elseif ($request->status == 'habis') {
                $query->where('status_tersedia', 0);
            }
        }

        $menus = $query->orderBy('id_menu', 'desc')->paginate(10)->withQueryString();

        return view('admin.menu', compact('menus'));
    }

    public function create()
    {
        if (!session()->has('staf_id')) {
            return redirect('/admin/login');
        }
        return view('admin.tambah-menu');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga' => 'required|numeric',
            'kategori' => 'required|string|max:50',
            'status_tersedia' => 'required|integer',
        ]);

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'status_tersedia' => $request->status_tersedia,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil dihapus!');
    }
}
