@extends('layouts.pelanggan')

@section('title', 'Pilih Menu - Restoran')

@section('content')

{{-- Search --}}
<div class="relative mb-4">
    <input
        type="text"
        id="search-menu"
        placeholder="Cari menu..."
        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white
               focus:outline-none focus:ring-2 focus:ring-orange-500
               focus:border-transparent text-sm shadow-sm"
    >
    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
</div>

{{-- Kategori --}}
<div class="flex gap-2 mb-5 overflow-x-auto scrollbar-hide pb-1 touch-pan-x">
    <button
        type="button"
        data-kategori="semua"
        class="cat-btn px-4 py-1.5 border text-xs font-bold rounded-full
               whitespace-nowrap transition bg-orange-500 text-white
               border-transparent shadow-sm shrink-0"
    >
        Semua
    </button>

    @foreach($kategoris as $kategori)
        <button
            type="button"
            data-kategori="{{ strtolower($kategori) }}"
            class="cat-btn px-4 py-1.5 border text-xs font-bold rounded-full
                   whitespace-nowrap transition bg-white text-gray-600
                   border-gray-200 hover:bg-gray-50 shrink-0"
        >
            {{ $kategori }}
        </button>
    @endforeach
</div>

{{-- Grid menu --}}
<div
    id="menu-grid"
    class="grid grid-cols-2 min-[420px]:grid-cols-2 md:grid-cols-3
           gap-2.5 min-[420px]:gap-3 md:gap-4"
>
    @forelse($menus as $menu)
        @php
            $isHabis = in_array($menu->status_tersedia, ['habis', '0', 0], true);
        @endphp

        <div
            class="menu-item bg-white rounded-2xl border border-gray-100
                   shadow-sm overflow-hidden flex flex-col min-w-0 relative
                   {{ $isHabis ? 'opacity-75' : '' }}"
            data-nama="{{ strtolower($menu->nama_menu) }}"
            data-kategori="{{ strtolower($menu->kategori) }}"
        >
            {{-- Gambar --}}
            <div class="aspect-[4/3] bg-gray-100 w-full overflow-hidden relative">
                @if(!empty($menu->gambar))
                    <img
                        src="{{ asset('uploads/menu/' . $menu->gambar) }}"
                        alt="{{ $menu->nama_menu }}"
                        loading="lazy"
                        class="w-full h-full object-cover
                               {{ $isHabis ? 'grayscale brightness-75' : '' }}"
                    >
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-utensils text-3xl sm:text-4xl"></i>
                    </div>
                @endif

                @if($isHabis)
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <span
                            class="bg-red-600 text-white font-extrabold
                                   text-[10px] sm:text-xs px-2.5 sm:px-3 py-1
                                   rounded uppercase tracking-wider shadow-md
                                   border border-white/20"
                        >
                            Habis
                        </span>
                    </div>
                @endif
            </div>

            {{-- Detail Menu --}}
<div class="p-3 sm:p-3.5 flex flex-col flex-1 min-w-0">
    <span
        class="text-[10px] sm:text-xs text-orange-600 bg-orange-50
               px-2 py-0.5 rounded-full w-fit font-semibold
               max-w-full truncate"
    >
        {{ $menu->kategori ?? 'Makanan' }}
    </span>

    <h3
        class="font-bold text-sm sm:text-base mt-1.5 text-gray-900
               leading-snug line-clamp-2 min-h-[2.5rem] sm:min-h-[3rem]"
    >
        {{ $menu->nama_menu ?? 'Nama Menu' }}
    </h3>

    <div class="flex items-center justify-between gap-2 mt-auto pt-3">
        <span
            class="font-extrabold text-gray-800
                   text-sm sm:text-base leading-tight
                   whitespace-nowrap"
        >
            Rp {{ number_format($menu->harga ?? 15000, 0, ',', '.') }}
        </span>

        @if($isHabis)
            <button
                type="button"
                disabled
                class="py-1.5 px-2.5 sm:px-3 bg-gray-300
                       text-gray-500 rounded-lg text-xs
                       font-bold cursor-not-allowed shrink-0"
            >
                Habis
            </button>
        @else
            <button
                type="button"
                onclick="addToCart(
                    '{{ $menu->id_menu ?? 1 }}',
                    '{{ addslashes($menu->nama_menu) }}',
                    {{ $menu->harga ?? 15000 }},
                    '{{ addslashes($menu->gambar ?? '') }}'
                )"
                class="py-1.5 px-2.5 sm:px-3 bg-orange-500
                       text-white rounded-lg text-xs
                       font-bold hover:bg-orange-600
                       active:bg-orange-700 transition
                       flex items-center gap-1 shadow-sm shrink-0"
            >
                <i class="fa-solid fa-plus text-[9px]"></i>
                <span>Tambah</span>
            </button>
        @endif
    </div>
</div>
        </div>
    @empty
        <div
            id="empty-state"
            class="col-span-full py-12 text-center text-gray-400"
        >
            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
            <p class="text-sm font-semibold">Belum ada menu tersedia.</p>
        </div>
    @endforelse
</div>

{{-- Hasil pencarian kosong --}}
<div
    id="no-result"
    class="hidden col-span-full py-12 text-center text-gray-400"
>
    <i class="fa-solid fa-magnifying-glass text-4xl mb-2"></i>
    <p class="text-sm font-semibold">Menu tidak ditemukan.</p>
</div>

<script>
    const storageKey = 'cart_meja_{{ $meja->token }}';
    let currentKategori = 'semua';

    // Tambahkan menu ke localStorage
    function addToCart(id, nama, harga, gambar) {
        const cart = JSON.parse(localStorage.getItem(storageKey)) || [];
        const existing = cart.find(item => item.id == id);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, nama, harga, gambar, qty: 1 });
        }

        localStorage.setItem(storageKey, JSON.stringify(cart));

        if (typeof updateCartCount === 'function') updateCartCount();
        if (typeof animateCartBadge === 'function') animateCartBadge();

        if (typeof showNotification === 'function') {
            showNotification(`${nama} berhasil ditambahkan!`);
        } else {
            alert(`${nama} berhasil ditambahkan ke keranjang!`);
        }
    }

    // Terapkan search + kategori
    function applyFilter() {
        const query = document.getElementById('search-menu')?.value
            .trim()
            .toLowerCase() || '';

        const items = document.querySelectorAll('.menu-item');
        const noResult = document.getElementById('no-result');
        let visibleCount = 0;

        items.forEach(item => {
            const nama = item.dataset.nama || '';
            const kategori = item.dataset.kategori || '';

            const matchKategori =
                currentKategori === 'semua' ||
                kategori === currentKategori;

            const matchQuery = nama.includes(query);
            const visible = matchKategori && matchQuery;

            item.classList.toggle('hidden', !visible);

            if (visible) visibleCount++;
        });

        noResult?.classList.toggle('hidden', visibleCount > 0);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-menu');
        const catButtons = document.querySelectorAll('.cat-btn');

        searchInput?.addEventListener('input', applyFilter);

        catButtons.forEach(button => {
            button.addEventListener('click', () => {
                currentKategori = button.dataset.kategori;

                catButtons.forEach(btn => {
                    btn.className =
                        'cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-white text-gray-600 border-gray-200 hover:bg-gray-50 shrink-0';
                });

                button.className =
                    'cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-orange-500 text-white border-transparent shadow-sm shrink-0';

                applyFilter();
            });
        });
    });
</script>

@endsection