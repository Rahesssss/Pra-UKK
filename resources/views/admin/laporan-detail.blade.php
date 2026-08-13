@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@section('header-title', 'Detail Pesanan #' . str_pad($pesanan->daily_order_number, 3, '0', STR_PAD_LEFT))

@section('content')

<div class="space-y-5">

    {{-- Header Detail Pesanan --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Detail Pesanan
            </h1>

            <div class="flex items-center gap-2 mt-2">
                {{-- Nomor pesanan --}}
                <span class="inline-flex items-center px-2 py-1 rounded-md bg-orange-50 text-orange-600 text-xs font-semibold">
                    #ORD-{{ str_pad($pesanan->daily_order_number, 3, '0', STR_PAD_LEFT) }}
                </span>

                {{-- Tanggal pesanan --}}
                <span class="text-xs text-gray-500">
                    {{ $pesanan->created_at->format('d M Y, H:i') }}
                </span>
            </div>
        </div>

        {{-- Tombol kembali --}}
        <a
            href="{{ route('admin.laporan') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali
        </a>
    </div>

    {{-- Card Daftar Menu --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">

        {{-- Header Card --}}
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200">
            <h2 class="text-base sm:text-lg font-bold text-gray-800">
                Daftar Menu
            </h2>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-600">
                        <th class="px-4 py-3 font-semibold">Gambar</th>
                        <th class="px-4 py-3 font-semibold">Nama Menu</th>
                        <th class="px-4 py-3 font-semibold text-center">Jumlah</th>
                        <th class="px-4 py-3 font-semibold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pesanan->detailPesanan as $detail)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- Gambar --}}
                        <td class="px-4 py-3">
                            @if($detail->menu && $detail->menu->gambar)
                            <img
                                src="{{ asset('uploads/menu/' . $detail->menu->gambar) }}"
                                alt="{{ $detail->menu->nama_menu }}"
                                class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @else
                            <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                <i class="fa-solid fa-image text-gray-400"></i>
                            </div>
                            @endif
                        </td>

                        {{-- Nama Menu --}}
                        <td class="px-4 py-3">
                            @if($detail->menu)
                            <p class="text-sm font-semibold text-gray-800">{{ $detail->menu->nama_menu }}</p>
                            @else
                            <p class="text-sm text-gray-400">Menu tidak ditemukan</p>
                            @endif
                        </td>

                        {{-- Jumlah --}}
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm font-semibold text-gray-700">{{ $detail->jumlah }}</span>
                        </td>

                        {{-- Subtotal --}}
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-medium text-gray-700">
                                Rp {{ number_format($detail->total, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-receipt text-gray-400"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Belum ada menu dalam pesanan ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Total Pesanan --}}
        <div class="border-t border-gray-200 px-4 sm:px-5 py-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-600">Total Pembayaran</span>
                <span class="text-lg font-bold text-gray-900">
                    Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

@endsection