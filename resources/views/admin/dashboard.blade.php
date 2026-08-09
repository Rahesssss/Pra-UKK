@extends('layouts.admin')

@section('title', 'Antrean Pesanan - Admin Resto')
@section('header-title', 'Antrean Pesanan')

@section('content')
@php
    $pesanans = \App\Models\Pesanan::with(['meja', 'detailPesanan.menu'])
        ->whereIn('status_pesanan', ['Menunggu', 'Sedang Dimasak', 'Selesai'])
        ->orderBy('created_at', 'desc')
        ->get();
@endphp

{{-- ACTION BAR (Filter & Refresh) --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-2 relative" id="filter-wrapper">
        <button id="btn-filter" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fa-solid fa-filter"></i> Filter
        </button>

        <div id="date-dropdown" class="hidden absolute top-full left-0 mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-xl p-4 z-50 dropdown-animate">
            <div class="text-sm font-semibold text-gray-700 mb-3">Pilih Tanggal</div>
            <input type="date" id="dp-input" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 mb-3 bg-gray-50" />

            <div class="grid grid-cols-3 gap-2 mb-4">
                <button class="dp-shortcut px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-orange-500 hover:bg-orange-50 hover:text-orange-600 transition" data-target="today">Hari Ini</button>
                <button class="dp-shortcut px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-orange-500 hover:bg-orange-50 hover:text-orange-600 transition" data-target="yesterday">Kemarin</button>
                <button class="dp-shortcut px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-orange-500 hover:bg-orange-50 hover:text-orange-600 transition" data-target="week">7 Hari</button>
            </div>

            <div class="flex justify-end gap-2">
                <button id="dp-cancel" class="px-4 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">Batal</button>
                <button id="dp-apply" class="px-4 py-1.5 bg-orange-500 rounded-lg text-xs font-semibold text-white hover:bg-orange-600 transition">Terapkan</button>
            </div>
        </div>

        <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-600 min-w-[120px] justify-center">
            <i class="fa-regular fa-calendar"></i>
            <span id="label-tanggal">Hari Ini</span>
        </div>
    </div>

    <button onclick="window.location.reload()" class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-semibold hover:bg-orange-600 transition shadow-sm">
        <i class="fa-solid fa-rotate-right"></i> Refresh
    </button>
</div>

{{-- KANBAN BOARD --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 items-start">
    {{-- Kolom Menunggu --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden min-h-[250px] md:min-h-[500px] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                <h3 class="font-bold text-gray-800">Menunggu</h3>
            </div>
            <span class="w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">{{ $menungguCount = $pesanans->where('status_pesanan', 'Menunggu')->count() }}</span>
        </div>
        <div class="p-3 flex flex-col gap-3 flex-1">
            @php
                $menungguItems = $pesanans->where('status_pesanan', 'Menunggu');
            @endphp
            @forelse($menungguItems as $pesanan)
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-orange-600">#ORD-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] text-gray-400">{{ $pesanan->created_at->diffForHumans() }}</span>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-1">{{ $pesanan->meja->nama_meja }}</h4>
                    <ul class="text-xs text-gray-600 mb-2 list-disc list-inside">
                        @foreach($pesanan->detailPesanan as $detail)
                            <li>{{ $detail->jumlah }}x {{ $detail->menu->nama_menu }}</li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                        <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        <form action="{{ route('admin.dashboard.status', $pesanan->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_pesanan" value="Sedang Dimasak">
                            <button type="submit" class="px-2 py-1 bg-orange-400 text-white text-[10px] rounded hover:bg-orange-500">Masak</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center flex-1 text-gray-400 text-sm gap-2 py-12">
                    <i class="fa-solid fa-inbox text-3xl"></i>
                    <p>Belum ada pesanan</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Kolom Sedang Dimasak --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden min-h-[250px] md:min-h-[500px] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <h3 class="font-bold text-gray-800">Sedang Dimasak</h3>
            </div>
            <span class="w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">{{ $pesanans->where('status_pesanan', 'Sedang Dimasak')->count() }}</span>
        </div>
        <div class="p-3 flex flex-col gap-3 flex-1">
            @forelse($pesanans->where('status_pesanan', 'Sedang Dimasak') as $pesanan)
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-blue-600">#ORD-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] text-gray-400">{{ $pesanan->created_at->diffForHumans() }}</span>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-1">{{ $pesanan->meja->nama_meja }}</h4>
                    <ul class="text-xs text-blue-800 mb-2 list-disc list-inside">
                        @foreach($pesanan->detailPesanan as $detail)
                            <li>{{ $detail->jumlah }}x {{ $detail->menu->nama_menu }}</li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between items-center mt-3 pt-2 border-t border-blue-200">
                        <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        <form action="{{ route('admin.dashboard.status', $pesanan->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_pesanan" value="Selesai">
                            <button type="submit" class="px-2 py-1 bg-green-500 text-white text-[10px] rounded hover:bg-green-600">Selesai</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center flex-1 text-gray-400 text-sm gap-2 py-12">
                    <i class="fa-solid fa-fire-burner text-3xl"></i>
                    <p>Tidak ada yang dimasak</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Kolom Selesai --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden min-h-[250px] md:min-h-[500px] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                <h3 class="font-bold text-gray-800">Selesai (Auto-hide 3m)</h3>
            </div>
            <span class="w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">{{ $pesanans->where('status_pesanan', 'Selesai')->count() }}</span>
        </div>
        <div class="p-3 flex flex-col gap-3 flex-1">
            @forelse($pesanans->where('status_pesanan', 'Selesai') as $pesanan)
                <div class="bg-green-50 p-3 rounded-lg border border-green-200 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-green-600">#ORD-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] text-gray-400">{{ $pesanan->updated_at->format('H:i') }}</span>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-1">{{ $pesanan->meja->nama_meja }}</h4>
                    <span class="text-xs font-bold text-green-700">Sudah Selesai</span>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center flex-1 text-gray-400 text-sm gap-2 py-12">
                    <i class="fa-solid fa-circle-check text-3xl"></i>
                    <p>Belum ada yang selesai</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const btnFilter = document.getElementById('btn-filter');
    const dateDropdown = document.getElementById('date-dropdown');
    const dpInput = document.getElementById('dp-input');
    const labelTanggal = document.getElementById('label-tanggal');
    const shortcuts = document.querySelectorAll('.dp-shortcut');

    function toInputVal(date) {
        return date.toISOString().split('T')[0];
    }

    function setActiveShortcut(targetName) {
        shortcuts.forEach(b => {
            if (b.dataset.target === targetName) {
                b.classList.remove('border-gray-200', 'text-gray-600');
                b.classList.add('border-orange-500', 'bg-orange-50', 'text-orange-600');
            } else {
                b.classList.remove('border-orange-500', 'bg-orange-50', 'text-orange-600');
                b.classList.add('border-gray-200', 'text-gray-600');
            }
        });
    }

    dpInput.value = toInputVal(new Date());
    setActiveShortcut('today');

    btnFilter.addEventListener('click', (e) => {
        e.stopPropagation();
        dateDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('filter-wrapper').contains(e.target)) {
            dateDropdown.classList.add('hidden');
        }
    });

    dpInput.addEventListener('change', () => {
        shortcuts.forEach(b => {
            b.classList.remove('border-orange-500', 'bg-orange-50', 'text-orange-600');
            b.classList.add('border-gray-200', 'text-gray-600');
        });
    });

    shortcuts.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetBtn = e.target;
            const action = targetBtn.dataset.target;
            const d = new Date();

            if (action === 'yesterday') d.setDate(d.getDate() - 1);
            if (action === 'week') d.setDate(d.getDate() - 6);

            dpInput.value = toInputVal(d);
            setActiveShortcut(action);
        });
    });

    document.getElementById('dp-apply').addEventListener('click', () => {
        const val = dpInput.value;
        if (val) {
            const todayVal = toInputVal(new Date());
            let activeTarget = null;
            shortcuts.forEach(b => {
                if (b.classList.contains('border-orange-500')) {
                    activeTarget = b.dataset.target;
                }
            });

            if (val === todayVal || activeTarget === 'today') {
                labelTanggal.textContent = 'Hari Ini';
            } else if (activeTarget === 'yesterday') {
                labelTanggal.textContent = 'Kemarin';
            } else if (activeTarget === 'week') {
                labelTanggal.textContent = '7 Hari Terakhir';
            } else {
                const d = new Date(val);
                labelTanggal.textContent = d.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            }
        }
        dateDropdown.classList.add('hidden');
    });

    document.getElementById('dp-cancel').addEventListener('click', () => {
        dateDropdown.classList.add('hidden');
    });
</script>
@endpush