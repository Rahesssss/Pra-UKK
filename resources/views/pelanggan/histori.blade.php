@extends('layouts.pelanggan')

@section('title', 'Histori Pesanan - ' . $meja->nama_meja)

@section('content')
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            animation: fadeIn 0.35s ease-out forwards;
        }
    </style>

    <div class="bg-gray-50 text-gray-800 antialiased font-sans pb-12 page-transition min-h-screen">
        <main class="max-w-md mx-auto px-4 py-5 space-y-4">

            @forelse($orders as $order)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm space-y-3">

                    {{-- HEAD CARD --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <span class="text-xs text-gray-400 block">ID Pesanan</span>
                            <span class="font-bold text-sm text-gray-900">
                                #ORD-{{ str_pad($nomorUrut[$order->id] ?? $order->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">
                                {{ $order->created_at->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <div class="text-right">
                            {{-- Badge Status --}}
                            @if($order->status_pesanan == 'Menunggu')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                    <i class="fa-solid fa-clock text-[10px] animate-pulse"></i> Menunggu
                                </span>
                            @elseif($order->status_pesanan == 'Sedang Dimasak')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                    <i class="fa-solid fa-fire text-[10px] animate-bounce"></i> Diproses
                                </span>
                            @elseif($order->status_pesanan == 'Selesai')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Dibatalkan
                                </span>
                            @endif

                            <span class="text-[10px] text-gray-400 block mt-1">
                                {{ $order->created_at->format('H:i') }} WIB
                            </span>
                        </div>
                    </div>

                    {{-- RINCIAN MENU --}}
                    <div class="space-y-2 py-1">
                        @foreach($order->detailPesanan as $detail)
                            <div class="space-y-1">
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

                                {{-- Catatan Per Item --}}
                                @if($detail->catatan_item)
                                    <div class="flex items-start gap-1.5 ml-7">
                                        <i class="fa-solid fa-note-sticky text-amber-400 text-[9px] mt-0.5 shrink-0"></i>
                                        <span class="text-[10px] text-amber-700 italic leading-snug">
                                            {{ $detail->catatan_item }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Catatan Umum Pesanan --}}
                    @if($order->catatan)
                        <div class="flex items-start gap-2 px-3 py-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                            <i class="fa-solid fa-note-sticky text-amber-500 text-xs shrink-0 mt-0.5"></i>
                            <div class="min-w-0">
                                <p class="text-[9px] font-bold text-amber-600 uppercase tracking-wide mb-0.5">
                                    Catatan Anda
                                </p>
                                <p class="text-[11px] text-amber-800 leading-snug">
                                    {{ $order->catatan }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- FOOTER CARD --}}
                    <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs bg-gray-50 -mx-4 -mb-4 p-3 rounded-b-2xl">
                        <span class="text-gray-500 font-medium">Total Pembayaran</span>
                        <span class="text-sm font-extrabold text-orange-600">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            @empty
                {{-- EMPTY STATE --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm my-6">
                    <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-base mb-1">Belum Ada Riwayat Pesanan</h3>
                    <p class="text-xs text-gray-400 mb-5">Anda belum pernah melakukan pemesanan di meja ini.</p>
                    <a
                        href="{{ route('pelanggan.menu', $meja->token) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-orange-600 transition"
                    >
                        <i class="fa-solid fa-utensils"></i> Pesan Sekarang
                    </a>
                </div>
            @endforelse

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'cart_meja_{{ $meja->token }}';
            const noteStorageKey = 'note_meja_{{ $meja->token }}';
            
            // Hapus keranjang dan catatan setelah sukses checkout
            localStorage.removeItem(storageKey);
            localStorage.removeItem(noteStorageKey);
        });
    </script>
@endsection
