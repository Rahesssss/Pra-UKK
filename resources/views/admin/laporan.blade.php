@extends('layouts.admin')

@section('title', 'Laporan Pendapatan')
@section('header-title', 'Laporan')

@section('content')
<div class="bg-white rounded-xl p-4 sm:p-6 mb-6 shadow-sm border border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-800">Filter Laporan</h3>
<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 border border-orange-200 text-orange-700 text-xs font-semibold rounded-full">
            <i class="fa-regular fa-calendar"></i>
            {{ $labelPeriode }}
        </span>
    </div>
<div class="flex gap-2 mb-5 border-b border-gray-200">
        <button
            type="button"
            id="tab-bulanan"
            onclick="switchTab('bulanan')"
            class="tab-btn pb-2.5 px-1 text-sm font-semibold border-b-2 transition-colors duration-150
                {{ $filterMode === 'bulanan' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}"
        >
            <i class="fa-regular fa-calendar-days mr-1"></i> Per Bulan
        </button>
        <button
            type="button"
            id="tab-custom"
            onclick="switchTab('custom')"
            class="tab-btn pb-2.5 px-1 text-sm font-semibold border-b-2 transition-colors duration-150
                {{ $filterMode === 'custom' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}"
        >
            <i class="fa-regular fa-calendar-range mr-1"></i> Rentang Tanggal
        </button>
    </div>
<form
        id="form-bulanan"
        method="GET"
        action="{{ route('admin.laporan') }}"
        class="{{ $filterMode === 'bulanan' ? '' : 'hidden' }}"
    >
        <input type="hidden" name="filter_mode" value="bulanan">

        <div class="flex flex-wrap items-end gap-3">
<div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-gray-500 font-medium">Bulan</label>
                <select
                    name="bulan"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white min-w-[140px]"
                >
                    @foreach($listBulan as $num => $nama)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
<div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-gray-500 font-medium">Tahun</label>
                <select
                    name="tahun"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white min-w-[100px]"
                >
                    @foreach($listTahun as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
<button
                type="submit"
                class="w-full sm:w-auto px-5 py-2 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition text-sm"
            >
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Tampilkan
            </button>
@if($bulan != now()->month || $tahun != now()->year)
                <a
                    href="{{ route('admin.laporan', ['filter_mode' => 'bulanan', 'bulan' => now()->month, 'tahun' => now()->year]) }}"
                    class="w-full sm:w-auto text-center text-sm text-gray-500 hover:text-orange-600 hover:underline py-2 transition"
                >
                    Bulan Ini
                </a>
            @endif
        </div>
<div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-gray-400 mr-1">Navigasi:</span>
            @php
                $prevDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
                $nextDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->addMonth();
                $isCurrentMonth = ($bulan == now()->month && $tahun == now()->year);
            @endphp
<a
                href="{{ route('admin.laporan', ['filter_mode' => 'bulanan', 'bulan' => $prevDate->month, 'tahun' => $prevDate->year]) }}"
                class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-orange-400 hover:text-orange-600 hover:bg-orange-50 transition"
            >
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                {{ $listBulan[$prevDate->month] }} {{ $prevDate->year }}
            </a>
@if(!$isCurrentMonth)
                <a
                    href="{{ route('admin.laporan', ['filter_mode' => 'bulanan', 'bulan' => $nextDate->month, 'tahun' => $nextDate->year]) }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-orange-400 hover:text-orange-600 hover:bg-orange-50 transition"
                >
                    {{ $listBulan[$nextDate->month] }} {{ $nextDate->year }}
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @endif
        </div>
    </form>
<form
        id="form-custom"
        method="GET"
        action="{{ route('admin.laporan') }}"
        class="{{ $filterMode === 'custom' ? '' : 'hidden' }}"
    >
        <input type="hidden" name="filter_mode" value="custom">

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-gray-500 font-medium">Tanggal Mulai</label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $start_custom }}"
                    max="{{ now()->toDateString() }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-gray-500 font-medium">Tanggal Akhir</label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $end_custom }}"
                    max="{{ now()->toDateString() }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>
            <button
                type="submit"
                class="w-full sm:w-auto px-5 py-2 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition text-sm"
            >
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Terapkan
            </button>
            <a
                href="{{ route('admin.laporan') }}"
                class="w-full sm:w-auto text-center text-sm text-gray-500 hover:underline py-2"
            >
                Reset
            </a>
        </div>
    </form>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
<div class="bg-white rounded-xl p-4 sm:p-6 border border-green-200 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm text-gray-500 mb-1">Total Pendapatan</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $labelPeriode }}</p>
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
                <p class="text-xs text-gray-400 mt-0.5">Pesanan lunas</p>
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
                    {{ $menuTerlaris?->menu?->nama_menu ?? 'Belum ada data' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $menuTerlaris?->total_qty ?? 0 }} porsi terjual
                </p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                <i class="fa-solid fa-star text-orange-600 text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
<div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h3 class="font-bold text-gray-900 text-base sm:text-lg">Daftar Pesanan</h3>
            <p class="text-xs sm:text-sm text-gray-500">
                Pesanan lunas periode <span class="font-semibold text-gray-700">{{ $labelPeriode }}</span>
            </p>
        </div>
<span class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg font-medium self-start sm:self-auto">
            {{ $totalPesanan }} pesanan
        </span>
    </div>
<div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">No</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">ID Pesanan</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Tanggal</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Meja</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Items</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                    <th class="py-3 px-3 sm:px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
                    <th class="py-3 px-3 sm:px-6 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pesanans as $index => $pesanan)
                    <tr class="hover:bg-gray-50 transition-colors duration-100">
<td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm text-gray-500">
                                {{ $pesanans->firstItem() + $index }}
                            </span>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 text-xs sm:text-sm">
                                    #ORD-{{ str_pad($pesanan->daily_order_number, 3, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-[10px] text-gray-400">
                                    {{ $pesanan->created_at->format('H:i') }} WIB
                                </span>
                            </div>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm text-gray-600">
                                {{ $pesanan->created_at->translatedFormat('d M Y') }}
                            </span>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            <span class="text-xs sm:text-sm font-medium text-gray-700">
                                {{ $pesanan->meja->nama_meja ?? '-' }}
                            </span>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-700 rounded-full text-xs font-bold">
                                {{ $pesanan->detailPesanan->sum('jumlah') }}
                            </span>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            <span class="font-bold text-gray-900 text-xs sm:text-sm">
                                Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                            </span>
                        </td>
<td class="py-3 px-3 sm:px-6">
                            @if($pesanan->status_pesanan == 'Menunggu')
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-clock text-[9px]"></i> Menunggu
                                </span>
                            @elseif($pesanan->status_pesanan == 'Sedang Dimasak')
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-fire text-[9px]"></i> Dimasak
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai
                                </span>
                            @endif
                        </td>
<td class="py-3 px-3 sm:px-6 text-center">
                            <a
                                href="{{ route('admin.laporan.detail', $pesanan->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-medium hover:bg-blue-600 transition"
                            >
                                <i class="fa-solid fa-eye text-[10px]"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-300">
                                <i class="fa-solid fa-inbox text-5xl"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Tidak ada pesanan</p>
                                    <p class="text-xs text-gray-300">pada periode {{ $labelPeriode }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@if($pesanans->hasPages())
        <div class="p-4 sm:p-6 border-t border-gray-200">
            {{ $pesanans->links() }}
        </div>
    @endif
<div class="px-4 sm:px-6 py-3 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1 text-xs text-gray-500">
        <span>
            Menampilkan
            <span class="font-semibold text-gray-700">{{ $pesanans->firstItem() ?? 0 }}</span>
            –
            <span class="font-semibold text-gray-700">{{ $pesanans->lastItem() ?? 0 }}</span>
            dari
            <span class="font-semibold text-gray-700">{{ $pesanans->total() }}</span>
            pesanan lunas
        </span>
        <span class="text-gray-400">Periode: {{ $labelPeriode }}</span>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(mode) {
        const formBulanan = document.getElementById('form-bulanan');
        const formCustom = document.getElementById('form-custom');
        const tabBulanan = document.getElementById('tab-bulanan');
        const tabCustom = document.getElementById('tab-custom');

        const active = ['border-orange-500', 'text-orange-600'];
        const inactive = ['border-transparent', 'text-gray-400'];
        const isBulanan = mode === 'bulanan';

        (isBulanan ? formBulanan : formCustom).classList.remove('hidden');
        (isBulanan ? formCustom : formBulanan).classList.add('hidden');

        const activeTab = isBulanan ? tabBulanan : tabCustom;
        const inactiveTab = isBulanan ? tabCustom : tabBulanan;

        activeTab.classList.add(...active);
        activeTab.classList.remove(...inactive);
        inactiveTab.classList.remove(...active);
        inactiveTab.classList.add(...inactive);

        (isBulanan ? formBulanan : formCustom)
            .querySelector('[name="filter_mode"]').value = mode;
    }
</script>
@endpush