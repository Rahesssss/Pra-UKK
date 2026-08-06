@extends('layouts.pelanggan')

@section('title', 'Pilih Menu - Restoran')

@section('content')
    {{-- Search Bar --}}
    <div class="relative mb-4">
        <input type="text" id="search-menu" oninput="filterMenu()" placeholder="Cari menu..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm shadow-sm">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
    </div>

    {{-- Category Tabs --}}
    <div class="flex gap-2 mb-5 overflow-x-auto scrollbar-hide pb-1">
        
        {{-- Tombol "Semua" --}}
        <button type="button" data-kategori="semua" class="cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-orange-500 text-white border-transparent shadow-sm">
            Semua
        </button>
        
        {{-- Looping Kategori dari Database --}}
        @foreach($kategoris as $kategori)
            <button type="button" data-kategori="{{ strtolower($kategori) }}" class="cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-white text-gray-600 border-gray-200 hover:bg-gray-50">
                {{ $kategori }}
            </button>
        @endforeach
        
    </div>

    {{-- Grid Daftar Menu --}}
    <div id="menu-grid" class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
        @forelse($menus as $menu)
        <div class="menu-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col relative" data-nama="{{ strtolower($menu->nama_menu) }}" data-kategori="{{ strtolower($menu->kategori) }}">
            
            {{-- Gambar Menu --}}
            <div class="h-32 bg-gray-100 w-full overflow-hidden relative">
                @if(!empty($menu->gambar))
                    <img src="{{ asset('uploads/menu/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-utensils text-4xl"></i>
                    </div>
                @endif
            </div>

            {{-- Detail Menu --}}
            <div class="p-3 flex flex-col flex-1">
                <span class="text-[10px] text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full w-fit font-semibold">{{ $menu->kategori ?? 'Makanan' }}</span>
                <h3 class="font-bold text-sm mt-1.5 text-gray-900 leading-tight">{{ $menu->nama_menu ?? 'Nama Menu' }}</h3>
                <!-- <p class="text-xs text-gray-400 line-clamp-1 mt-0.5 mb-2">{{ $menu->deskripsi ?? 'Deskripsi menu' }}</p> -->
                
                <div class="flex items-center justify-between mt-auto pt-2">
                    <span class="font-extrabold text-gray-800 text-sm">Rp {{ number_format($menu->harga ?? 15000, 0, ',', '.') }}</span>
                    
                    <button onclick="tambahKeKeranjang('{{ $menu->id_menu ?? 1 }}', '{{ $menu->nama_menu }}', {{ $menu->harga ?? 15000 }})" class="py-1.5 px-3 bg-orange-500 text-white rounded-lg text-xs font-bold hover:bg-orange-600 transition flex items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-plus text-[9px]"></i> Tambah
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div id="empty-state" class="col-span-full py-12 text-center text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
            <p class="text-sm font-semibold">Belum ada menu tersedia.</p>
        </div>
        @endforelse
    </div>
    
    <div id="no-result" class="hidden col-span-full py-12 text-center text-gray-400">
        <i class="fa-solid fa-magnifying-glass text-4xl mb-2"></i>
        <p class="text-sm font-semibold">Menu tidak ditemukan.</p>
    </div>

    <script>
        let currentKategori = 'semua';

        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Fitur Search via Input
            const searchInput = document.getElementById('search-menu');
            if(searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }

            // 2. Fitur Klik Tab Kategori
            const catButtons = document.querySelectorAll('.cat-btn');
            
            catButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Set kategori saat ini berdasarkan tombol yang diklik
                    currentKategori = this.getAttribute('data-kategori');
                    
                    // Kembalikan semua tombol ke warna default (Putih)
                    catButtons.forEach(b => {
                        b.className = 'cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-white text-gray-600 border-gray-200 hover:bg-gray-50';
                    });
                    
                    // Ubah HANYA tombol yang sedang diklik menjadi warna Oranye
                    this.className = 'cat-btn px-4 py-1.5 border text-xs font-bold rounded-full whitespace-nowrap transition bg-orange-500 text-white border-transparent shadow-sm';
                    
                    // Terapkan filter ke daftar menu
                    applyFilter();
                });
            });

        });

        // 3. Fungsi Utama untuk Menyaring (Filter) Data
        function applyFilter() {
            const searchInput = document.getElementById('search-menu');
            const query = searchInput ? searchInput.value.toLowerCase() : '';
            const items = document.querySelectorAll('.menu-item');
            let visibleCount = 0;

            items.forEach(item => {
                const nama = item.getAttribute('data-nama') || '';
                const kategori = item.getAttribute('data-kategori') || '';
                
                const matchKategori = (currentKategori === 'semua' || kategori === currentKategori);
                const matchQuery = nama.includes(query);

                if (matchKategori && matchQuery) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Tampilkan Pesan "Menu tidak ditemukan" jika kosong
            const noResult = document.getElementById('no-result');
            if (noResult) {
                if (visibleCount === 0) {
                    noResult.classList.remove('hidden');
                } else {
                    noResult.classList.add('hidden');
                }
            }
        }
    </script>
@endsection
