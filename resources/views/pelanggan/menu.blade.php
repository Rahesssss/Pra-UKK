@extends('layouts.pelanggan')

@section('title', 'Pilih Menu')

@section('content')

{{-- Toast Container --}}
<div id="toast" class="fixed bottom-20 left-1/2 -translate-x-1/2 z-[999] pointer-events-none">
</div>

{{-- Search Bar --}}
<div class="relative mb-4">
    <input
        type="text"
        id="search-menu"
        placeholder="Cari menu..."
        class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-200 bg-white
            focus:outline-none focus:ring-2 focus:ring-orange-400
            focus:border-transparent text-sm shadow-sm"
    >
    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
    <button
        type="button"
        id="clear-search"
        onclick="clearSearch()"
        class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full
            bg-gray-100 text-gray-400 items-center justify-center
            hover:bg-gray-200 transition"
        style="display: none;"
    >
        <i class="fa-solid fa-xmark text-xs"></i>
    </button>
</div>

{{-- Kategori --}}
<div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-1 px-1" style="scrollbar-width: none;">
    <button
        type="button"
        data-kategori="semua"
        class="cat-btn px-4 py-1.5 text-xs font-bold rounded-full
            whitespace-nowrap shrink-0 bg-orange-500 text-white border border-transparent"
    >
        Semua
    </button>

    @foreach($kategoris as $kategori)
        <button
            type="button"
            data-kategori="{{ strtolower($kategori) }}"
            class="cat-btn px-4 py-1.5 text-xs font-bold rounded-full
                whitespace-nowrap shrink-0 bg-white text-gray-600
                border border-gray-200 hover:border-orange-300"
        >
            {{ $kategori }}
        </button>
    @endforeach
</div>

{{-- Grid Menu --}}
<div id="menu-grid" class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">

    @forelse($menus as $menu)
        @php
            $isHabis = in_array($menu->status_tersedia, ['habis', '0', 0], true);
        @endphp

        <div
            class="menu-item bg-white rounded-2xl border border-gray-100
                shadow-sm overflow-hidden flex flex-col relative
                {{ $isHabis ? 'opacity-60' : '' }}"
            data-nama="{{ strtolower($menu->nama_menu) }}"
            data-kategori="{{ strtolower($menu->kategori) }}"
        >
            <div class="aspect-[4/3] bg-gray-100 w-full overflow-hidden relative">
                @if(!empty($menu->gambar))
                    <img
                        src="{{ asset('uploads/menu/' . $menu->gambar) }}"
                        alt="{{ $menu->nama_menu }}"
                        loading="lazy"
                        class="w-full h-full object-cover {{ $isHabis ? 'grayscale' : '' }}"
                        onerror="this.style.display='none'"
                    >
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-utensils text-3xl"></i>
                    </div>
                @endif

                @if($isHabis)
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <span class="bg-red-600 text-white font-bold text-[10px] px-3 py-1 rounded uppercase tracking-wide">
                            Habis
                        </span>
                    </div>
                @endif
            </div>

            <div class="p-3 flex flex-col flex-1">
                <span class="text-[10px] text-orange-600 bg-orange-50 px-2 py-0.5 rounded w-fit font-medium truncate">
                    {{ $menu->kategori ?? 'Menu' }}
                </span>

                <h3 class="font-bold text-sm mt-1.5 text-gray-900 leading-snug line-clamp-2 min-h-[2.5rem]">
                    {{ $menu->nama_menu }}
                </h3>

                <div class="flex items-end justify-between gap-1 mt-auto pt-2">
                    <span class="font-extrabold text-gray-800 text-sm whitespace-nowrap">
                        Rp {{ number_format($menu->harga, 0, ',', '.') }}
                    </span>

                    @if($isHabis)
                        <button disabled class="py-1.5 px-2.5 bg-gray-200 text-gray-400 rounded-lg text-[11px] font-bold cursor-not-allowed shrink-0">
                            Habis
                        </button>
                    @else
                        <button
                            type="button"
                            onclick="addToCart(
                                '{{ $menu->id_menu }}',
                                '{{ addslashes($menu->nama_menu) }}',
                                {{ $menu->harga }},
                                '{{ addslashes($menu->gambar ?? '') }}'
                            )"
                            class="py-1.5 px-3 bg-orange-500 text-white rounded-lg text-[11px] font-bold
                                hover:bg-orange-600 active:bg-orange-700 transition
                                flex items-center gap-1 shrink-0"
                        >
                            <i class="fa-solid fa-plus text-[9px]"></i>
                            <span>Tambah</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-12 text-center text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
            <p class="text-sm font-semibold">Belum ada menu tersedia.</p>
        </div>
    @endforelse
</div>

{{-- No Result --}}
<div id="no-result" class="hidden py-12 text-center text-gray-400">
    <i class="fa-solid fa-magnifying-glass text-3xl mb-2"></i>
    <p class="text-sm font-semibold">Menu tidak ditemukan.</p>
    <button type="button" onclick="clearSearch()" class="mt-3 text-xs font-bold text-orange-500 hover:text-orange-600">
        Reset Pencarian
    </button>
</div>

<script>
    const storageKey = 'cart_meja_{{ $meja->token }}';
    let currentKategori = 'semua';

    function addToCart(id, nama, harga, gambar) {
        const cart = JSON.parse(localStorage.getItem(storageKey)) || [];
        const existing = cart.find(item => String(item.id) === String(id));

        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                id: String(id),
                nama: nama,
                harga: Number(harga),
                gambar: gambar || '',
                qty: 1
            });
        }

        localStorage.setItem(storageKey, JSON.stringify(cart));

        if (typeof updateCartCount === 'function') updateCartCount();
        if (typeof animateCartBadge === 'function') animateCartBadge();

        // Toast sederhana
        toast(nama + ' ditambahkan');
    }

    // Toast simple
    function toast(msg) {
        const box = document.getElementById('toast');
        const el = document.createElement('div');
        el.className = 'bg-gray-800 text-white text-xs font-medium px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 mb-2 transition-all';
        el.innerHTML = '<i class="fa-solid fa-check text-green-400"></i>' + msg;
        box.appendChild(el);

        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-4px)';
            setTimeout(() => el.remove(), 200);
        }, 1800);
    }

    function clearSearch() {
        const input = document.getElementById('search-menu');
        if (input) input.value = '';
        document.getElementById('clear-search').style.display = 'none';
        applyFilter();
    }

    function applyFilter() {
        const query = (document.getElementById('search-menu')?.value || '').trim().toLowerCase();
        const items = document.querySelectorAll('.menu-item');
        const noResult = document.getElementById('no-result');
        const clearBtn = document.getElementById('clear-search');
        let visibleCount = 0;

        clearBtn.style.display = query.length > 0 ? 'flex' : 'none';

        items.forEach(item => {
            const nama = item.dataset.nama || '';
            const kategori = item.dataset.kategori || '';
            const show = (currentKategori === 'semua' || kategori === currentKategori) && nama.includes(query);
            item.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });

        noResult?.classList.toggle('hidden', visibleCount > 0);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-menu');
        const catButtons = document.querySelectorAll('.cat-btn');

        let t;
        searchInput?.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(applyFilter, 150);
        });

        catButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                currentKategori = btn.dataset.kategori;

                catButtons.forEach(b => {
                    b.className = 'cat-btn px-4 py-1.5 text-xs font-bold rounded-full whitespace-nowrap shrink-0 bg-white text-gray-600 border border-gray-200 hover:border-orange-300';
                });
                btn.className = 'cat-btn px-4 py-1.5 text-xs font-bold rounded-full whitespace-nowrap shrink-0 bg-orange-500 text-white border border-transparent';

                applyFilter();
            });
        });
    });
</script>

@endsection
