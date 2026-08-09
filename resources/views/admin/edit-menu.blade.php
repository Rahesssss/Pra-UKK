@extends('layouts.admin')

@section('title', 'Edit Menu - Admin Resto')
@section('header-title', 'Edit Data Menu')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl p-5 sm:p-6 shadow-sm">

        {{-- Notifikasi Error Validasi --}}
        @if ($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex gap-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- FORM EDIT DATA --}}
        <form id="form-edit" action="{{ route('admin.menu.update', $menu->id_menu) }}" method="POST" class="space-y-5" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ================= FOTO MENU ================= --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Menu</label>
                <input type="file" name="gambar" id="gambar" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">

                <label for="gambar" id="drop-area" class="border-2 border-dashed border-orange-300 rounded-xl h-64 flex flex-col justify-center items-center cursor-pointer hover:border-orange-500 transition bg-orange-50/30 overflow-hidden relative">

                    {{-- Konten Upload (Akan otomatis tersembunyi jika data $menu->gambar ada isinya) --}}
                    <div id="upload-content" class="flex flex-col items-center justify-center text-center p-4 {{ !empty($menu->gambar) ? 'hidden' : '' }}">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-orange-500"></i>
                        </div>
                        <p class="text-sm text-gray-600">Klik atau tarik gambar baru untuk mengganti</p>
                        <p class="text-xs text-gray-400 mt-2">Format JPG, PNG, WEBP. Maksimal 2 MB.</p>
                    </div>

                    {{-- PREVIEW / GAMBAR LAMA --}}
                    <img id="preview"
                        src="{{ !empty($menu->gambar) ? asset('uploads/menu/' . $menu->gambar) : '' }}"
                        class="{{ empty($menu->gambar) ? 'hidden' : '' }} absolute inset-0 w-full h-full object-cover rounded-xl">
                </label>
            </div>

            {{-- ================= NAMA MENU ================= --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition bg-gray-50">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- ================= KATEGORI ================= --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition bg-gray-50">
                        <option value="Paket Gorengan" {{ old('kategori') == 'Paket Gorengan' ? 'selected' : '' }}>Paket Gorengan</option>
                        <option value="Minuman" {{ old('kategori') == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                        <option value="Mie" {{ old('kategori') == 'Mie' ? 'selected' : '' }}>Mie</option>
                        <option value="Nasi" {{ old('kategori') == 'Nasi ' ? 'selected' : '' }}>Nasi</option>
                    </select>
                </div>

                {{-- ================= HARGA ================= --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-500 font-medium text-sm">Rp</span>

                        {{-- Input Hidden (Angka Asli) --}}
                        <input type="hidden" name="harga" id="harga_value" value="{{ old('harga', $menu->harga) }}">

                        {{-- Input Formatter (Tampilan) --}}
                        <input type="text" id="harga_formatter" required
                            value="{{ number_format(old('harga', $menu->harga), 0, ',', '.') }}"
                            class="pl-11 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition bg-gray-50">
                    </div>
                </div>
            </div>

            {{-- ================= STATUS KETERSEDIAAN ================= --}}
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-2">
                <div>
                    <h4 class="font-semibold text-sm text-gray-800">Ketersediaan Menu</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Tentukan apakah menu ini siap dipesan saat ini.</p>
                </div>

                @php 
                    // Mengambil nilai status ketersediaan, default 'tersedia' jika kosong
                    $status = old('status_tersedia', $menu->status_tersedia); 
                    $isChecked = ($status == 'tersedia');
                @endphp

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="statusToggle" class="sr-only" {{ $isChecked ? 'checked' : '' }}>
                    <input type="hidden" name="status_tersedia" id="statusValue" value="{{ $status }}">

                    <div class="w-12 h-7 rounded-full transition-colors duration-300 {{ $isChecked ? 'bg-orange-500' : 'bg-gray-300' }}" id="toggleBg"></div>
                    <div id="toggleCircle" class="absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-300 transform {{ $isChecked ? 'translate-x-5' : 'translate-x-0' }} shadow-sm"></div>
                    <span class="ml-3 text-sm font-bold {{ $isChecked ? 'text-gray-700' : 'text-red-500' }}" id="statusText">
                        {{ $isChecked ? 'Tersedia' : 'Habis' }}
                    </span>
                </label>
            </div>
        </form>

        {{-- ================= TOMBOL AKSI ================= --}}
        <div class="pt-6 border-t border-gray-100 mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Batal & Simpan --}}
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.menu.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm text-center flex-1 sm:flex-none">
                    Batal
                </a>
                {{-- Tombol ini mengirim form edit di atas menggunakan atribut form="form-edit" --}}
                <button type="submit" form="form-edit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-sm text-sm flex-1 sm:flex-none">
                    Simpan Perubahan
                </button>
            </div>

            {{-- Form Hapus (Terpisah agar aman) --}}
            <form action="{{ route('admin.menu.destroy', $menu->id_menu) }}" method="POST" onsubmit="return confirm('Peringatan: Yakin ingin menghapus menu ini secara permanen?')" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-bold hover:bg-red-100 transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash"></i> Hapus Menu
                </button>
            </form>

        </div>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. FORMATTER HARGA
        const formatterInput = document.getElementById('harga_formatter');
        const valueInput = document.getElementById('harga_value');

        formatterInput.addEventListener('input', function(e) {
            let angka = this.value.replace(/\D/g, '');
            valueInput.value = angka;
            this.value = angka !== '' ? new Intl.NumberFormat('id-ID').format(angka) : '';
        });

        // 2. PREVIEW & DRAG-DROP GAMBAR
        const input = document.getElementById("gambar");
        const preview = document.getElementById("preview");
        const uploadContent = document.getElementById("upload-content");
        const dropArea = document.getElementById("drop-area");

        function previewImage() {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove("hidden");
                uploadContent.classList.add("hidden");
            }
            reader.readAsDataURL(file);
        }

        input.addEventListener("change", previewImage);

        dropArea.addEventListener("dragover", function(e) {
            e.preventDefault();
            dropArea.classList.add("border-orange-500", "bg-orange-100");
        });

        dropArea.addEventListener("dragleave", function() {
            dropArea.classList.remove("border-orange-500", "bg-orange-100");
        });

        dropArea.addEventListener("drop", function(e) {
            e.preventDefault();
            dropArea.classList.remove("border-orange-500", "bg-orange-100");
            input.files = e.dataTransfer.files;
            previewImage();
        });

        // 3. TOGGLE STATUS (Diubah ke string 'tersedia' dan 'habis')
        const toggle = document.getElementById("statusToggle");
        const value = document.getElementById("statusValue");
        const circle = document.getElementById("toggleCircle");
        const bg = document.getElementById("toggleBg");
        const text = document.getElementById("statusText");

        toggle.addEventListener("change", function() {
            if (this.checked) {
                value.value = "tersedia";
                // Menggunakan manipulasi class Tailwind agar transisi mulus
                circle.classList.remove("translate-x-0");
                circle.classList.add("translate-x-5");
                
                bg.classList.replace("bg-gray-300", "bg-orange-500");
                text.innerText = "Tersedia";
                text.classList.remove("text-red-500");
                text.classList.add("text-gray-700");
            } else {
                value.value = "habis";
                // Kembalikan ke posisi awal
                circle.classList.remove("translate-x-5");
                circle.classList.add("translate-x-0");
                
                bg.classList.replace("bg-orange-500", "bg-gray-300");
                text.innerText = "Habis";
                text.classList.remove("text-gray-700");
                text.classList.add("text-red-500");
            }
        });
    });
</script>
@endsection