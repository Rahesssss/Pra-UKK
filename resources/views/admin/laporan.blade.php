@extends('layouts.admin')

@section('title', 'Laporan Pendapatan')
@section('header-title', 'Laporan')

@section('content')
    {{-- Filter Date --}}
    <div class="bg-white rounded-xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200">
        <h3 class="font-bold text-gray-800 mb-4">Filter Laporan</h3>
        <form method="GET" action="{{ route('admin.laporan') }}" class="flex flex-wrap items-end gap-3 sm:gap-4">
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs sm:text-sm text-gray-600">Tanggal Mulai</label>
                <input type="date" name="start_date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ request('start_date') }}">
            </div>
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs sm:text-sm text-gray-600">Tanggal Akhir</label>
                <input type="date" name="end_date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ request('end_date') }}">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition text-sm">
                Terapkan
            </button>
            @if(request('start_date') || request('end_date'))
            <a href="{{ route('admin.laporan') }}" class="w-full sm:w-auto text-center text-sm text-gray-500 hover:underline py-2">Reset Filter</a>
            @endif
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl p-4 sm:p-6 border border-green-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">Total Pendapatan</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-wave text-green-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-6 border border-blue-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">Total Pesanan</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalPesanan }}</p>
                    <p class="text-xs text-gray-400">Pesanan</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt text-blue-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-6 border border-orange-200 shadow-sm sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">Menu Terlaris</p>
                    <p class="text-base sm:text-lg font-bold text-gray-900">
                        @if($menuTerlaris)
                            {{ $menuTerlaris->menu->nama_menu ?? 'N/A' }}
                        @else
                            Belum ada data
                        @endif
                    </p>
                    <p class="text-xs text-gray-500">
                        @if($menuTerlaris)
                            {{ $menuTerlaris->total_qty ?? 0 }} porsi terjual
                        @else
                            0 pesanan
                        @endif
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-star text-orange-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Daftar Pesanan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200">
            <h3 class="font-bold text-gray-900 text-base sm:text-lg">Daftar Pesanan Lunas</h3>
            <p class="text-xs sm:text-sm text-gray-500">Pesanan dengan status pembayaran Lunas.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">No</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">ID Pesanan</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">Meja</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">Total Item</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">Total</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">Waktu</th>
                        <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="py-3 px-3 sm:px-6 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $index => $pesanan)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm text-gray-600">{{ $pesanans->firstItem() + $index }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            <span class="font-medium text-gray-900 text-xs sm:text-sm">#ORD-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm font-medium text-gray-700">{{ $pesanan->meja->nama_meja ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm text-gray-700">{{ $pesanan->detailPesanan->sum('jumlah') }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            <span class="font-bold text-gray-900 text-xs sm:text-sm">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm text-gray-500">{{ $pesanan->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </td>
                        <td class="py-3 px-3 sm:px-6">
                            @if($pesanan->status_pesanan == 'Menunggu')
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Menunggu</span>
                            @elseif($pesanan->status_pesanan == 'Sedang Dimasak')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Dimasak</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Selesai</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 sm:px-6 text-center">
                            <a href="{{ route('admin.laporan.detail', $pesanan->id) }}" class="inline-block px-3 py-1 bg-blue-500 text-white rounded-lg text-xs font-medium hover:bg-blue-600 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-inbox text-3xl sm:text-4xl mb-2"></i>
                            <p class="text-sm">Belum ada pesanan lunas dalam periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($pesanans->hasPages())
        <div class="p-4 sm:p-6 border-t border-gray-200">
            {{ $pesanans->links() }}
        </div>
        @endif
        
        <div class="p-4 sm:p-6 border-t border-gray-200 text-xs sm:text-sm text-gray-500">
            Menampilkan <span class="font-semibold">{{ $pesanans->count() }}</span> pesanan dari <span class="font-semibold">{{ $pesanans->total() }}</span> total pesanan lunas.
        </div>
    </div>
@endsection