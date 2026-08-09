@extends('layouts.admin')

@section('title', 'Manajemen Meja')
@section('header-title', 'Daftar Meja')

@section('content')
<div class="space-y-6">

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Top Bar: Info & Tambah Meja Button --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Daftar Meja</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola penempatan dan kode QR untuk pemesanan.</p>
        </div>
        <button onclick="openAddModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-sm shrink-0">
            <i class="fa-solid fa-plus"></i> Tambah Meja
        </button>
    </div>

    {{-- Grid Kartu Meja: Bersebelahan (2 di HP, 3 di Tablet, 4 di Laptop/Desktop dengan ukuran responsif) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
        @forelse($mejas as $meja)
        <div class="bg-white border border-gray-200 rounded-2xl p-3 md:p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                {{-- Header Card --}}
                <div class="flex items-center justify-between mb-2">    
                    <h3 class="font-bold text-gray-800 text-sm md:text-base truncate">{{ $meja->nama_meja }}</h3>
                    <div class="flex items-center gap-1.5 text-gray-400 shrink-0">
                        {{-- Tombol Edit --}}
                        <button onclick="openEditModal('{{ $meja->id_meja }}', '{{ $meja->nama_meja }}', '{{ $meja->status }}')" class="hover:text-blue-600 transition p-1" title="Edit Meja">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        {{-- Tombol Hapus --}}
                        <form action="{{ route('admin.meja.destroy', $meja->id_meja) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $meja->nama_meja }} secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:text-red-600 transition p-1" title="Hapus Meja">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Status Badge --}}
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

                {{-- QR Box: Mengecil otomatis di tablet/HP agar bisa bersebelahan banyak, normal di laptop --}}
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-2.5 flex flex-col items-center justify-center mb-3">
                    @php
                        $urlPemesanan = url('/menu/' . $meja->token);
                        $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($urlPemesanan);
                    @endphp
                    <img src="{{ $qrImage }}" alt="QR Code" class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 object-contain mix-blend-multiply">
                    <span class="text-[9px] md:text-[10px] text-gray-400 font-mono mt-1 bg-gray-200 px-1.5 py-0.5 rounded tracking-wide">
                        {{ $meja->token }}
                    </span>
                </div>
            </div>

            {{-- Button Print QR --}}
            <button onclick="downloadQR('{{ $qrImage }}', 'QR_{{ $meja->nama_meja }}.png')" class="w-full py-1.5 md:py-2 bg-white border border-gray-300 hover:border-gray-400 rounded-xl text-xs font-semibold text-gray-700 flex items-center justify-center gap-1.5 transition shadow-xs">
                <i class="fa-solid fa-print text-[10px]"></i> Print QR
            </button>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-gray-400">
            <i class="fa-solid fa-chair text-4xl mb-3"></i>
            <p class="text-base font-semibold text-gray-600">Belum ada meja</p>
            <p class="text-xs text-gray-400 mt-1">Silakan klik Tambah Meja untuk memulai.</p>
        </div>
        @endforelse
    </div>

</div>

{{-- MODAL TAMBAH MEJA --}}
<div id="addModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Tambah Meja Baru</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        
        <form action="{{ route('admin.meja.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nomor / Nama Meja</label>
                    <input type="text" name="nama_meja" placeholder="Contoh: Meja 5" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Active">Active</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600">Simpan Meja</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT MEJA --}}
<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Edit Meja & Status</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        
        <form id="formEditMeja" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nomor / Nama Meja</label>
                    <input type="text" id="edit_nama_meja" name="nama_meja" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Status Meja</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Active">Active (Bisa Discan/Pesan)</option>
                        <option value="Nonaktif">Nonaktif (Diblokir)</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('flex');
        document.getElementById('addModal').classList.add('hidden');
    }

    function openEditModal(id, nama, status) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
        
        document.getElementById('edit_nama_meja').value = nama;
        document.getElementById('edit_status').value = status;
        document.getElementById('formEditMeja').action = '/admin/meja/' + id;
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('flex');
        document.getElementById('editModal').classList.add('hidden');
    }

    // Function untuk download QR code dengan JavaScript Blob
    async function downloadQR(url, filename) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal mengambil gambar QR');
            
            const blob = await response.blob();
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        } catch (error) {
            console.error('Download error:', error);
            alert('Gagal mendownload QR Code. Silakan coba lagi atau refresh halaman.');
        }
    }
</script>
@endsection