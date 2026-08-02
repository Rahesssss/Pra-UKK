@extends('layouts.admin')

@section('title', 'Manajemen Menu - Admin Resto')
@section('header-title', 'Daftar Menu')

@section('content')
<div class="space-y-6">

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Baris Atas: Search Bar & Tombol Tambah Menu --}}
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">

        {{-- Form Pencarian --}}
        <form action="{{ route('admin.menu.index') }}" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama menu..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 shadow-sm">
            </div>
            {{-- Pertahankan status filter jika sedang aktif saat search --}}
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </form>

        {{-- Tombol Tambah Menu --}}
        <a href="{{ route('admin.menu.create') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-sm shrink-0">
            <i class="fa-solid fa-plus"></i> Tambah Menu
        </a>
    </div>

    {{-- Tombol Filter di Bawah Search Bar (Semua, Tersedia, Habis) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        @php
        $currentStatus = request('status', 'semua');
        $currentSearch = request('search');
        @endphp

        <a href="{{ route('admin.menu.index', ['search' => $currentSearch, 'status' => 'semua']) }}"
            class="px-4 py-1.5 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus == 'semua' ? 'bg-orange-500 text-white shadow-orange-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Semua
        </a>

        <a href="{{ route('admin.menu.index', ['search' => $currentSearch, 'status' => 'tersedia']) }}"
            class="px-4 py-1.5 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus == 'tersedia' ? 'bg-green-600 text-white shadow-green-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Tersedia
        </a>

        <a href="{{ route('admin.menu.index', ['search' => $currentSearch, 'status' => 'habis']) }}"
            class="px-4 py-1.5 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus == 'habis' ? 'bg-red-600 text-white shadow-red-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Habis
        </a>

        @if($currentSearch || ($currentStatus && $currentStatus != 'semua'))
        <a href="{{ route('admin.menu.index') }}" class="text-xs font-semibold text-gray-400 hover:text-gray-600 ml-2">Reset Filter</a>
        @endif
    </div>

    {{-- Tabel Daftar Menu --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-3 sm:px-6">No</th>
                        <th class="py-3 px-3 sm:px-6">Nama Menu</th>
                        <th class="py-3 px-3 sm:px-6">Kategori</th>
                        <th class="py-3 px-3 sm:px-6">Harga</th>
                        <th class="py-3 px-3 sm:px-6">Status Ketersediaan</th>
                        <th class="py-3 px-3 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($menus as $index => $menu)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-medium text-gray-500">{{ $menus->firstItem() + $index }}</td>
                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-bold text-gray-800">{{ $menu->nama_menu }}</td>
                        <td class="py-3 sm:py-4 px-3 sm:px-6 text-gray-600">
                            <span class="px-2.5 py-1 bg-gray-100 rounded-lg text-xs font-semibold text-gray-700">{{ $menu->kategori }}</span>
                        </td>
                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-semibold text-gray-900">Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                        <td class="py-3 sm:py-4 px-3 sm:px-6">
                            @if($menu->status_tersedia == 1)
                            <span class="px-2.5 py-1 bg-green-50 text-green-600 border border-green-200 rounded-full text-xs font-bold">Tersedia</span>
                            @else
                            <span class="px-2.5 py-1 bg-red-50 text-red-600 border border-red-200 rounded-full text-xs font-bold">Habis</span>
                            @endif
                        </td>
                        <td class="py-3 sm:py-4 px-3 sm:px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.menu.destroy', $menu->id_menu) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Hapus Menu">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center text-gray-400">
                            <i class="fa-solid fa-burger text-4xl mb-3"></i>
                            <p class="text-base font-semibold text-gray-600">Menu tidak ditemukan</p>
                            <p class="text-xs text-gray-400 mt-1">Coba kata kunci pencarian lain atau klik kategori filter di atas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Navigasi Pagination --}}
        @if($menus->hasPages())
        <div class="p-4 bg-gray-50 border-t border-gray-200">
            {{ $menus->links() }}
        </div>
        @endif
    </div>

</div>
@endsection