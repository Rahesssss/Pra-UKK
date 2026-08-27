@extends('layouts.admin')

@section('title', 'Antrean Pesanan - Admin Resto')
@section('header-title', 'Antrean Pesanan')

@section('content')

@php
    // Konfigurasi tampilan setiap status
    $columns = [
        'Menunggu' => [
            'dot' => 'bg-gray-400',
            'card' => 'bg-gray-50 border-gray-200',
            'text' => 'text-orange-600',
            'detail' => 'text-gray-600',
            'border' => 'border-gray-200',
            'button' => 'bg-orange-400 hover:bg-orange-500',
            'next' => 'Sedang Dimasak',
            'icon' => 'fa-fire-burner',
            'action' => 'Masak',
            'empty' => 'Belum ada pesanan',
            'emptyIcon' => 'fa-inbox',
        ],
        'Sedang Dimasak' => [
            'dot' => 'bg-blue-500',
            'card' => 'bg-blue-50 border-blue-200',
            'text' => 'text-blue-600',
            'detail' => 'text-blue-800',
            'border' => 'border-blue-200',
            'button' => 'bg-green-500 hover:bg-green-600',
            'next' => 'Selesai',
            'icon' => 'fa-check',
            'action' => 'Selesai',
            'empty' => 'Tidak ada yang dimasak',
            'emptyIcon' => 'fa-fire-burner',
        ],
        'Selesai' => [
            'dot' => 'bg-green-500',
            'card' => 'bg-green-50 border-green-200',
            'text' => 'text-green-600',
            'detail' => 'text-gray-500',
            'border' => 'border-green-200',
            'button' => '',
            'next' => null,
            'icon' => 'fa-circle-check',
            'action' => 'Selesai',
            'empty' => 'Belum ada yang selesai',
            'emptyIcon' => 'fa-circle-check',
        ],
    ];

    // Kelompokkan pesanan sekali agar tidak where() berulang
    $groupedPesanans = $pesanans->groupBy('status_pesanan');
@endphp

{{-- Filter & header --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
    <div class="flex items-center gap-2 relative" id="filter-wrapper">
        <button id="btn-filter" type="button"
            class="flex items-center gap-2 px-3 md:px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fa-solid fa-filter"></i>
            <span>Filter</span>
        </button>

        <div id="date-dropdown"
            class="hidden absolute top-full left-0 mt-2 w-[calc(100vw-2rem)] max-w-72 bg-white border border-gray-200 rounded-xl shadow-xl p-4 z-50">
            <div class="text-sm font-semibold text-gray-700 mb-3">Pilih Tanggal</div>

            <input type="date" id="dp-input" value="{{ $tanggal }}" max="{{ now()->toDateString() }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 mb-3 bg-gray-50">

            <div class="grid grid-cols-3 gap-2 mb-4">
                <button type="button" class="dp-shortcut px-2 py-1.5 border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-medium transition" data-target="today">
                    Hari Ini
                </button>
                <button type="button" class="dp-shortcut px-2 py-1.5 border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-medium transition" data-target="yesterday">
                    Kemarin
                </button>
                <button type="button" class="dp-shortcut px-2 py-1.5 border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-medium transition" data-target="week">
                    7 Hari
                </button>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" id="dp-cancel"
                    class="px-4 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" id="dp-apply"
                    class="px-4 py-1.5 bg-orange-500 rounded-lg text-xs font-semibold text-white hover:bg-orange-600 transition">
                    Terapkan
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 px-3 md:px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-600 min-w-[130px] md:min-w-[140px] justify-center">
            <i class="fa-regular fa-calendar"></i>
            <span id="label-tanggal">{{ $tanggalLabel }}</span>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @if(!$isToday)
            <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700 font-medium">
                <i class="fa-solid fa-clock-rotate-left shrink-0"></i>
                <span>
                    Menampilkan data:
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                </span>
            </div>
        @endif

        <a href="{{ route('admin.dashboard', ['tanggal' => $tanggal]) }}"
            class="flex items-center gap-2 px-3 md:px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-semibold hover:bg-orange-600 transition shadow-sm">
            <i class="fa-solid fa-rotate-right"></i>
            <span>Refresh</span>
        </a>
    </div>
</div>

{{-- HP: 1 kolom | iPad: 2 kolom | Desktop: 3 kolom --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6 items-start">
    @foreach($columns as $status => $column)
        @php 
            $orders = $groupedPesanans->get($status, collect()); 
            
            // FILTER BACKEND: Cegah order selesai tampil di awal jika umurnya > 5 menit
            if ($status === 'Selesai' && $isToday) {
                $orders = $orders->filter(function($pesanan) {
                    // Hanya tampilkan jika waktu selesainya belum lewat 5 menit
                    return $pesanan->updated_at->diffInMinutes(now()) < 5;
                });
            }
        @endphp

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden min-h-[250px] md:min-h-[450px] xl:min-h-[500px] flex flex-col">
            <div class="flex items-center justify-between p-3 md:p-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full {{ $column['dot'] }} shrink-0"></span>
                    <h3 class="font-bold text-gray-800 truncate">
                        {{ $status }}
                        @if($status === 'Selesai' && $isToday)
                            <span class="text-xs font-normal text-gray-400">(Auto-hide 5m)</span>
                        @endif
                    </h3>
                </div>

                {{-- Tambahkan ID unik pada badge agar JS bisa mengurangi angka otomatis --}}
                <span id="badge-{{ Str::slug($status) }}" class="w-6 h-6 shrink-0 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm transition-all duration-300">
                    {{ $orders->count() }}
                </span>
            </div>

            <div class="p-2.5 md:p-3 flex flex-col gap-3 flex-1 overflow-y-auto" id="container-{{ Str::slug($status) }}">
                @forelse($orders as $pesanan)
                    {{-- Tambahkan class 'selesai-card' dan data attribute untuk timer JS --}}
                    <div class="{{ $column['card'] }} p-3 rounded-lg border shadow-sm hover:shadow-md transition-all duration-500 @if($status === 'Selesai') selesai-card @endif" 
                         @if($status === 'Selesai') data-time="{{ $pesanan->updated_at->timestamp }}" @endif>
                         
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="text-xs font-bold {{ $column['text'] }}">
                                #ORD-{{ str_pad($nomorUrut[$pesanan->id] ?? 0, 3, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="text-[10px] text-gray-400 text-right whitespace-nowrap">
                                @if($status === 'Selesai')
                                    Selesai {{ $pesanan->updated_at->format('H:i') }}
                                @else
                                    {{ $pesanan->created_at->diffForHumans() }}
                                @endif
                            </span>
                        </div>

                        <h4 class="font-bold text-gray-900 mb-2 truncate">
                            {{ $pesanan->meja->nama_meja }}
                        </h4>

                        <ul class="text-xs {{ $column['detail'] }} mb-2 space-y-1">
                            @foreach($pesanan->detailPesanan as $detail)
                                <li class="flex items-start gap-1">
                                    <span class="shrink-0">•</span>
                                    <span class="wrap-break-word">
                                        {{ $detail->jumlah }}x {{ $detail->menu->nama_menu }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        @if($pesanan->catatan)
                            <div class="flex items-start gap-2 px-2.5 py-2 bg-amber-50 border border-amber-200 rounded-lg mt-2 mb-2">
                                <i class="fa-solid fa-note-sticky text-amber-500 text-xs shrink-0 mt-0.5"></i>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-amber-600 uppercase tracking-wide mb-0.5">
                                        Catatan Dapur
                                    </p>
                                    <p class="text-[11px] text-amber-800 leading-snug break-words">
                                        {{ $pesanan->catatan }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-between items-center gap-2 mt-3 pt-2 border-t {{ $column['border'] }}">
                            <span class="font-bold text-gray-900 text-sm whitespace-nowrap">
                                Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                            </span>

                            @if($column['next'])
                                <form action="{{ route('admin.dashboard.status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status_pesanan" value="{{ $column['next'] }}">
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                                    <button type="submit"
                                        class="px-3 py-1.5 {{ $column['button'] }} text-white text-[10px] font-semibold rounded-lg transition whitespace-nowrap">
                                        <i class="fa-solid {{ $column['icon'] }} mr-1"></i>
                                        {{ $column['action'] }}
                                    </button>
                                </form>
                            @else
                                <span class="flex items-center gap-1 text-xs font-bold text-green-700 whitespace-nowrap">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Selesai
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div id="empty-state-{{ Str::slug($status) }}" class="flex flex-col items-center justify-center flex-1 text-gray-400 text-sm gap-2 py-12">
                        <i class="fa-solid {{ $column['emptyIcon'] }} text-3xl"></i>
                        <p>{{ $column['empty'] }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Date Picker
    const routeDashboard = @json(route('admin.dashboard'));
    const filterWrapper = document.getElementById('filter-wrapper');
    const btnFilter = document.getElementById('btn-filter');
    const dateDropdown = document.getElementById('date-dropdown');
    const dateInput = document.getElementById('dp-input');
    const btnApply = document.getElementById('dp-apply');
    const btnCancel = document.getElementById('dp-cancel');
    const shortcuts = document.querySelectorAll('.dp-shortcut');

    const closeDropdown = () => dateDropdown.classList.add('hidden');

    btnFilter.addEventListener('click', e => {
        e.stopPropagation();
        dateDropdown.classList.toggle('hidden');
    });

    btnCancel.addEventListener('click', closeDropdown);

    btnApply.addEventListener('click', () => {
        if (dateInput.value) {
            window.location.href = `${routeDashboard}?tanggal=${encodeURIComponent(dateInput.value)}`;
        }
    });

    shortcuts.forEach(button => {
        button.addEventListener('click', () => {
            const date = new Date();
            const target = button.dataset.target;

            if (target === 'yesterday') date.setDate(date.getDate() - 1);
            if (target === 'week') date.setDate(date.getDate() - 7);

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            dateInput.value = `${year}-${month}-${day}`;
        });
    });

    document.addEventListener('click', e => {
        if (!filterWrapper.contains(e.target)) closeDropdown();
    });

    // Auto Hide 5 Menit
    const isToday = @json($isToday);
    
    if (isToday) {
        setInterval(() => {
            // Ambil waktu saat ini (dalam detik)
            const now = Math.floor(Date.now() / 1000); 
            const selesaiCards = document.querySelectorAll('.selesai-card');
            
            selesaiCards.forEach(card => {
                const updatedTime = parseInt(card.getAttribute('data-time'));
                
                // Jika selisih waktu sudah lebih dari 5 menit (300 detik)
                if (now - updatedTime >= 300) {
                    
                    // Animasi mengecil dan menghilang
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    
                    // Hapus elemen dari DOM setelah animasi selesai
                    setTimeout(() => {
                        card.remove();
                        
                        // Kurangi jumlah angka pada badge atas
                        const badgeSelesai = document.getElementById('badge-selesai');
                        if(badgeSelesai) {
                            let count = parseInt(badgeSelesai.innerText);
                            if(count > 0) {
                                badgeSelesai.innerText = count - 1;
                            }
                            
                            // Jika sudah tidak ada card, kembalikan tampilan "Belum ada yang selesai"
                            if(count - 1 === 0) {
                                const containerSelesai = document.getElementById('container-selesai');
                                containerSelesai.innerHTML = `
                                    <div id="empty-state-selesai" class="flex flex-col items-center justify-center flex-1 text-gray-400 text-sm gap-2 py-12">
                                        <i class="fa-solid fa-circle-check text-3xl"></i>
                                        <p>Belum ada yang selesai</p>
                                    </div>
                                `;
                            }
                        }
                    }, 500); // 500ms mengikuti duration-500 di tailwind class
                }
            });
        }, 10000); // Mengecek secara otomatis setiap 10 detik
    }
});
</script>
@endpush