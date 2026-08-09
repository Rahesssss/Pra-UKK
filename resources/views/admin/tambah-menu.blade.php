@extends('layouts.admin')

@section('title', 'Tambah Menu - Admin Resto')
@section('header-title', 'Tambah Menu Makanan / Minuman')

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

        <form action="{{ route('admin.menu.store') }}" method="POST" class="space-y-5" enctype="multipart/form-data">
            @csrf

            {{-- ================= FOTO MENU ================= --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Menu</label>

                {{-- Input asli (disembunyikan) --}}
                <input type="file" name="gambar" id="gambar" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">

                {{-- Area Drag & Drop --}}
                <label for="gambar" id="drop-area" class="border-2 border-dashed border-orange-300 rounded-xl h-64 flex flex-col justify-center items-center cursor-pointer hover:border-orange-500 transition bg-orange-50/30 overflow-hidden relative">

                    {{-- Konten Info Upload --}}
                    <div id="upload-content" class="flex flex-col items-center justify-center text-center p-4">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-orange-500"></i>
                        </div>
                        <p class="text-sm text-gray-600">Tarik & lepas gambar di sini atau</p>
                        <p class="text-orange-500 font-semibold mt-1">Jelajahi File</p>
                        <p class="text-xs text-gray-400 mt-2">Format JPG, PNG, WEBP. Maksimal 2 MB.</p>
                    </div>

                    {{-- Preview Gambar --}}
                    <img id="preview" class="hidden absolute inset-0 w-full h-full object-cover rounded-xl">
                </label>
            </div>

            {{-- ================= NAMA MENU ================= --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" value="{{ old('nama_menu') }}" required placeholder="Contoh: Nasi Goreng Spesial"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- ================= KATEGORI ================= --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition bg-white">
                        <option value="" disabled selected>Pilih Kategori</option>
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

                        {{-- Input yang dilihat User (Format Titik) --}}
                        <input type="text" id="harga_formatter" placeholder="0" required
                            class="pl-11 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition">

                        {{-- PERBAIKAN: Input hidden yang dikirim ke Database (Tanpa Titik) --}}
                        <input type="hidden" name="harga" id="harga_value" value="{{ old('harga') }}">
                    </div>
                </div>
            </div>

            {{-- ================= STATUS KETERSEDIAAN ================= --}}
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-2">
                <div>
                    <h4 class="font-semibold text-sm text-gray-800">Ketersediaan Menu</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Tentukan apakah menu ini siap dipesan saat ini.</p>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <!-- Ubah nilai default checked dan hidden value menjadi 'tersedia' -->
                    <input type="checkbox" id="statusToggle" class="sr-only" checked>
                    <input type="hidden" name="status_tersedia" id="statusValue" value="tersedia">

                    <div class="w-12 h-7 bg-orange-500 rounded-full transition-colors duration-300" id="toggleBg"></div>
                    <div id="toggleCircle" class="absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-300 transform translate-x-5 shadow-sm"></div>
                    <span class="ml-3 text-sm font-bold text-gray-700" id="statusText">Tersedia</span>
                </label>
            </div>

            {{-- ================= TOMBOL SUBMIT ================= --}}
            <div class="flex justify-end gap-3 pt-6 mt-2">
                <a href="{{ route('admin.menu.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                    Batal
                </a>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-sm text-sm flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Menu
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. FORMATTER HARGA
        const formatterInput = document.getElementById('harga_formatter');
        const valueInput = document.getElementById('harga_value');

        // Jika ada nilai old (saat error validasi), format saat halaman dimuat
        if (valueInput.value) {
            formatterInput.value = new Intl.NumberFormat('id-ID').format(valueInput.value);
        }

        formatterInput.addEventListener('input', function(e) {
            // Hanya ambil karakter angka
            let angka = this.value.replace(/\D/g, '');
            // Simpan nilai asli ke hidden input untuk database
            valueInput.value = angka;
            // Format tampilan dengan titik ribuan
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