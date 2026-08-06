<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meja Tidak Aktif</title>
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-5 text-center font-sans">
    
    <div class="bg-white p-8 rounded-3xl shadow-sm max-w-sm w-full border border-gray-100">
        {{-- Ikon Silang / Dilarang --}}
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 text-4xl">
            <i class="fa-solid fa-ban"></i>
        </div>
        
        {{-- Judul Peringatan --}}
        <h2 class="text-xl font-extrabold text-gray-900 mb-2">Meja Dinonaktifkan</h2>
        
        {{-- Menampilkan Pesan dari Controller --}}
        <p class="text-sm text-gray-500 mb-6 leading-relaxed">
            {{ $pesan ?? 'Maaf, meja ini sedang dinonaktifkan dan tidak dapat digunakan untuk memesan saat ini.' }}
        </p>
        
        {{-- Tombol Bantuan --}}
        <button onclick="location.reload()" class="w-full py-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition mb-2">
            Muat Ulang Halaman
        </button>
        <p class="text-[10px] text-gray-400 mt-3">Silakan hubungi kasir atau pelayan jika ini adalah sebuah kesalahan.</p>
    </div>

</body>
</html>