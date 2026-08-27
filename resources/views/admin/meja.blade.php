@extends('layouts.admin')

@section('title', 'Manajemen Meja')
@section('header-title', 'Daftar Meja')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Daftar Meja</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola penempatan dan kode QR untuk pemesanan.</p>
        </div>
        <button onclick="openAddModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-sm shrink-0">
            <i class="fa-solid fa-plus"></i> Tambah Meja
        </button>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
        @forelse($mejas as $meja)
        <div class="bg-white border border-gray-200 rounded-2xl p-3 md:p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-800 text-sm md:text-base truncate">{{ $meja->nama_meja }}</h3>
                    <div class="flex items-center gap-1.5 text-gray-400 shrink-0">
                        <button onclick="openEditModal('{{ $meja->id_meja }}', '{{ $meja->nama_meja }}', '{{ $meja->status }}')" class="hover:text-blue-600 transition p-1" title="Edit">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <form action="{{ route('admin.meja.destroy', $meja->id_meja) }}" method="POST" onsubmit="return confirm('Yakin hapus {{ $meja->nama_meja }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="hover:text-red-600 transition p-1" title="Hapus">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mb-2.5">
                    @if($meja->status == 'Active')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-600 border border-green-100 rounded-full text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-full text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
                    </span>
                    @endif
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-2.5 flex flex-col items-center justify-center mb-3">
                    @php
                        $urlPemesanan = url('/menu/' . $meja->token);
                        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($urlPemesanan);
                        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
                    @endphp
                    <img src="{{ $qrBase64 }}" alt="QR Code"
                         class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 object-contain mix-blend-multiply"
                         id="qr-img-{{ $meja->id_meja }}">
                    <span class="text-[9px] md:text-[10px] text-gray-400 font-mono mt-1 bg-gray-200 px-1.5 py-0.5 rounded tracking-wide">
                        {{ $meja->token }}
                    </span>
                </div>
            </div>

            <button onclick="downloadQRAsPNG('qr-img-{{ $meja->id_meja }}', 'QR_{{ $meja->nama_meja }}')"
                    class="w-full py-1.5 md:py-2 bg-white border border-gray-300 hover:border-gray-400 rounded-xl text-xs font-semibold text-gray-700 flex items-center justify-center gap-1.5 transition">
                <i class="fa-solid fa-download text-[10px]"></i> Download QR
            </button>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-gray-400">
            <i class="fa-solid fa-chair text-4xl mb-3"></i>
            <p class="text-base font-semibold text-gray-600">Belum ada meja</p>
            <p class="text-xs text-gray-400 mt-1">Klik Tambah Meja untuk memulai.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- MODAL TAMBAH MEJA --}}
<div id="addModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAddModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-base">Tambah Meja Baru</h3>
                <button onclick="closeAddModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('admin.meja.store') }}" method="POST">
                @csrf
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Meja</label>
                        <input type="text" name="nama_meja" required placeholder="Contoh: Meja 1"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                        <select name="status" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition">
                            <option value="Active">Active</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT MEJA --}}
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-base">Edit Meja</h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Meja</label>
                        <input type="text" name="nama_meja" id="edit_nama_meja" required
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                        <select name="status" id="edit_status" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition">
                            <option value="Active">Active</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal Tambah
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

// Modal Edit
function openEditModal(id, nama, status) {
    document.getElementById('editForm').action = '/admin/meja/' + id;
    document.getElementById('edit_nama_meja').value = nama;
    document.getElementById('edit_status').value = status;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Download QR sebagai PNG
function downloadQRAsPNG(imgId, filename) {
    const img = document.getElementById(imgId);
    if (!img) { alert('QR tidak ditemukan.'); return; }

    // Buat canvas untuk convert SVG ke PNG
    const canvas = document.createElement('canvas');
    const size = 500; // resolusi output PNG
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');

    // Background putih
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, size, size);

    const image = new Image();
    image.onload = function() {
        // Padding agar QR tidak mentok di pinggir
        const padding = 40;
        ctx.drawImage(image, padding, padding, size - padding * 2, size - padding * 2);

        // Convert canvas ke PNG dan download
        const link = document.createElement('a');
        link.download = filename + '.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    image.onerror = function() {
        alert('Gagal memproses QR Code.');
    };

    image.src = img.src;
}
</script>
@endsection
