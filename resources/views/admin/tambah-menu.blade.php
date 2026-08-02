@extends('layouts.admin')

@section('title', 'Tambah Menu - Admin Resto')
@section('header-title', 'Tambah Menu Makanan / Minuman')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white border border-gray-200 rounded-xl p-5 sm:p-6 shadow-sm">

        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.menu.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" required placeholder="Contoh: Nasi Goreng Spesial" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Harga (Rp)</label>
                <input type="number" name="harga" required placeholder="Contoh: 25000" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none bg-white">
                    <option value="Makanan">Makanan</option>
                    <option value="Minuman">Minuman</option>
                    <option value="Snack">Snack / Cireng</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status Ketersediaan</label>
                <select name="status_tersedia" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="1">Tersedia</option>
                    <option value="0">Habis</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-orange-500 text-white rounded-lg text-sm font-semibold hover:bg-orange-600 transition shadow-sm">Simpan Menu</button>
            </div>
        </form>

    </div>
</div>
@endsection