@extends('layouts.pelanggan')

@section('title', 'Histori Pesanan - ' . $meja->nama_meja)

@section('content')
    {{-- Custom Keyframes untuk Transisi Halaman --}}
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>

    <div class="bg-slate-50 text-gray-800 antialiased font-sans pb-24 min-h-screen animate-fade-in">
        
        <main class="max-w-md mx-auto px-4 py-6 space-y-5">

            @forelse($orders as $order)
                <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm hover:shadow-md transition duration-200">
                    
                    {{-- HEADER CARD: ID, Tanggal & Status --}}
                    <div class="flex items-start justify-between border-b border-gray-100 pb-4 mb-4">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">ID Pesanan</span>
                            <span class="font-black text-sm text-gray-900">
                                #ORD-{{ str_pad($nomorUrut[$order->id] ?? $order->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[11px] text-gray-500 block mt-1 flex items-center gap-1">
                                <i class="fa-regular fa-calendar-days text-gray-400"></i>
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>

                        <div class="text-right">
                            {{-- Badge Status Dinamis --}}
                            @if($order->status_pesanan == 'Menunggu')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 shadow-sm">
                                    <i class="fa-solid fa-clock animate-pulse"></i> Menunggu
                                </span>
                            @elseif($order->status_pesanan == 'Sedang Dimasak')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200 shadow-sm">
                                    <i class="fa-solid fa-fire animate-bounce"></i> Diproses
                                </span>
                            @elseif($order->status_pesanan == 'Selesai')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                    <i class="fa-solid fa-circle-check"></i> Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold bg-red-50 text-red-600 border border-red-200 shadow-sm">
                                    <i class="fa-solid fa-circle-xmark"></i> Dibatalkan
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- RINCIAN MENU --}}
                    <div class="space-y-3 mb-4">
                        @foreach($order->detailPesanan as $detail)
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 bg-slate-50 border border-gray-100 text-gray-700 font-bold rounded-lg flex items-center justify-center text-[11px] shrink-0 mt-0.5">
                                        {{ $detail->jumlah }}x
                                    </span>
                                    <span class="font-semibold text-gray-800 leading-snug">
                                        {{ $detail->menu->nama_menu ?? 'Menu telah dihapus' }}
                                    </span>
                                </div>
                                <span class="text-gray-600 font-bold text-xs mt-1 shrink-0">
                                    Rp {{ number_format($detail->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- CATATAN UMUM --}}
                    @if($order->catatan)
                        <div class="flex items-start gap-2.5 px-3.5 py-3 bg-amber-50/70 border border-amber-100 rounded-2xl mb-4">
                            <i class="fa-solid fa-kitchen-set text-amber-500 text-xs shrink-0 mt-0.5"></i>
                            <div class="min-w-0">
                                <p class="text-[9px] font-bold text-amber-600 uppercase tracking-wide mb-0.5">
                                    Catatan Dapur
                                </p>
                                <p class="text-xs text-amber-900 leading-relaxed italic">
                                    "{{ $order->catatan }}"
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- FOOTER CARD: Total Bayar --}}
                    <div class="border-t border-dashed border-gray-200 pt-4 mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 font-medium">Total Pembayaran</span>
                        <span class="text-lg font-black text-orange-500">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            @empty
                {{-- EMPTY STATE: Ditampilkan jika belum ada riwayat --}}
                <div class="bg-white border border-dashed border-gray-300 rounded-3xl p-10 text-center shadow-sm my-6">
                    <div class="w-16 h-16 bg-orange-50 text-orange-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">Belum Ada Pesanan</h3>
                    <p class="text-sm text-gray-500 mb-6">Anda belum pernah memesan di meja ini.</p>
                    <a href="{{ route('pelanggan.menu', $meja->token) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white text-sm font-bold rounded-xl shadow-md hover:bg-orange-600 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-utensils"></i> Pesan Sekarang
                    </a>
                </div>
            @endforelse

        </main>
    </div>

    {{-- SCRIPT: Membersihkan Keranjang Saat Masuk Halaman Histori --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'cart_meja_{{ $meja->token }}';
            const noteStorageKey = 'note_meja_{{ $meja->token }}';
            
            // Hapus keranjang dan catatan setelah berhasil checkout / melihat histori
            localStorage.removeItem(storageKey);
            localStorage.removeItem(noteStorageKey);
        });
    </script>
@endsection