@extends('layouts.pelanggan')

@section('title', 'Histori Pesanan - ' . $meja->nama_meja)

@section('content')
    {{-- CSS Khusus Halaman Ini Saja (Animasi Transisi) --}}
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .page-transition {
            animation: fadeIn 0.35s ease-out forwards;
        }
    </style>

    <div class="bg-gray-50 text-gray-800 antialiased font-sans pb-12 page-transition min-h-screen">
        
        <!-- {{-- HEADER KELUAR / KEMBALI --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
            <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Tombol Kembali ke Menu --}}
                    <a href="{{ route('pelanggan.menu', $meja->token) }}" class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-xs">
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-gray-900 leading-tight">Histori Pesanan</h1>
                        <p class="text-xs text-orange-600 font-medium">Meja: <span class="font-bold bg-orange-100 px-2 py-0.5 rounded-md text-orange-700">{{ $meja->nama_meja }}</span></p>
                    </div>
                </div>

                {{-- Tombol Refresh / Muat Ulang --}}
                <button onclick="window.location.reload()" class="w-9 h-9 bg-orange-50 border border-orange-200 rounded-xl flex items-center justify-center text-orange-600 hover:bg-orange-100 transition shadow-xs" title="Refresh Status">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
            </div>
        </header> -->

        {{-- KONTEN UTAMA HISTORI --}}
        <main class="max-w-md mx-auto px-4 py-5 space-y-4">

            {{-- LOOPING DATA PESANAN --}}
            @forelse($orders as $order)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-xs space-y-3">
                    
                    {{-- HEAD CARD: ID, WAKTU & STATUS --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <span class="text-xs text-gray-400 block">ID Pesanan</span>
                            <span class="font-bold text-sm text-gray-900">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="text-right">
                            {{-- Logika Badge Status Pesanan --}}
                            @if($order->status_pesanan == 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                    <i class="fa-solid fa-clock text-[10px] animate-pulse"></i> Menunggu
                                </span>
                            @elseif($order->status_pesanan == 'diproses')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                    <i class="fa-solid fa-fire text-[10px] animate-bounce"></i> Diproses
                                </span>
                            @elseif($order->status_pesanan == 'selesai')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Dibatalkan
                                </span>
                            @endif

                            <span class="text-[10px] text-gray-400 block mt-1">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    </div>

                    {{-- RINCIAN MENU YANG DIPESAN --}}
                    <div class="space-y-2 py-1">
                        @foreach($order->detailPesanan as $detail)
                            <div class="flex justify-between items-center text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 bg-gray-100 text-gray-700 font-bold rounded-md flex items-center justify-center text-[10px]">
                                        {{ $detail->jumlah }}x
                                    </span>
                                    <span class="font-medium text-gray-800">
                                        {{ $detail->menu->nama_menu ?? 'Menu dihapus' }}
                                    </span>
                                </div>
                                <span class="text-gray-600 font-semibold">
                                    Rp {{ number_format($detail->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- FOOTER CARD: TOTAL & KETERANGAN --}}
                    <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs bg-gray-50 -mx-4 -mb-4 p-3 rounded-b-2xl">
                        <span class="text-gray-500 font-medium">Total Pembayaran</span>
                        <span class="text-sm font-extrabold text-orange-600">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            @empty
                {{-- TAMPILAN JIKA BELUM ADA HISTORI PESANAN --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-xs my-6">
                    <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-base mb-1">Belum Ada Riwayat Pesanan</h3>
                    <p class="text-xs text-gray-400 mb-5">Anda belum pernah melakukan pemesanan di meja ini.</p>
                    <a href="{{ route('pelanggan.menu', $meja->token) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-orange-600 transition">
                        <i class="fa-solid fa-utensils"></i> Pesan Sekarang
                    </a>
                </div>
            @endforelse

        </main>
    </div>
    <script>
        // Hapus keranjang setelah histori dimuat (menandakan pesanan sudah masuk)
        document.addEventListener('DOMContentLoaded', function() {
            const storageKey = 'cart_meja_{{ $meja->token }}';
            localStorage.removeItem(storageKey);
        });
    </script>
@endsection