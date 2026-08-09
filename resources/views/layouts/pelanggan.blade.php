<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Restoran')</title>
    {{-- Google Fonts Yeseva One --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Yeseva+One&display=swap" rel="stylesheet">
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .font-yeseva { font-family: 'Yeseva One', serif; }
    </style>
    
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans pb-24">

    {{-- Header / Top Bar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="font-yeseva text-md md:text-xl text-gray-900 tracking-wide">Warung Makan Haji Wiwin</h1>
            <p class="text-xs text-orange-600 font-semibold flex items-center gap-1 mt-0.5">
                    <i class="fa-solid fa-utensils"></i> Meja: 
                    <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-[11px]">{{ $meja->nama_meja ?? '-' }}</span>
            </p>
        </div>
    </header>

    {{-- Konten Utama Halaman --}}
    <main class="max-w-4xl mx-auto px-4 py-5">
        @yield('content')
    </main>

    {{-- Bottom Navigation (Footer Layout) --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <div class="max-w-4xl mx-auto px-4 py-3 grid grid-cols-3 text-center">
            <a href="{{ route('pelanggan.menu', $meja->token) }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('pelanggan.menu') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500' }}">
                <i class="fa-solid fa-book-open text-xl"></i>
                <span class="text-[10px] font-semibold">Menu</span>
            </a>
            <a href="{{ route('pelanggan.pesanan', $meja->token) }}" class="flex flex-col items-center justify-center gap-1 relative {{ request()->routeIs('pelanggan.pesanan') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500' }}">
                <div class="relative">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span id="cart-count-footer" class="absolute -top-2 -right-2 bg-red-500 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold">0</span>
                </div>
                <span class="text-[10px] font-semibold">Pesanan</span>
            </a>
            <a href="{{ route('pelanggan.histori', $meja->token) }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('pelanggan.histori') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500' }}">
                <i class="fa-solid fa-clipboard-list text-xl"></i>
                <span class="text-[10px] font-semibold">Histori Pesanan</span>
            </a>
        </div>
    </nav>

    {{-- Script Global untuk Keranjang (LocalStorage) --}}
    <script>
        function tambahKeKeranjang(id, nama, harga) {
            let token = '{{ $meja->token ?? "default" }}';
            let cart = JSON.parse(localStorage.getItem('cart_meja_' + token)) || [];
            let item = cart.find(p => p.id === id);
            
            if (item) {
                item.qty += 1;
            } else {
                cart.push({ id: id, nama: nama, harga: harga, qty: 1 });
            }
            
            localStorage.setItem('cart_meja_' + token, JSON.stringify(cart));
            
            // Update counter langsung tanpa delay
            updateCartCount();
            
            // Animasi badge counter
            animateCartBadge();
            
            // Notifikasi
            showNotification(nama + ' ditambahkan ke keranjang!');
        }

        function updateCartCount() {
            let token = '{{ $meja->token ?? "default" }}';
            let cart = JSON.parse(localStorage.getItem('cart_meja_' + token)) || [];
            let totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
            
            let cartCountEl = document.getElementById('cart-count-footer');
            if(cartCountEl) {
                cartCountEl.innerText = totalQty;
                cartCountEl.style.display = totalQty > 0 ? 'flex' : 'none';
            }
        }

        function animateCartBadge() {
            let cartCountEl = document.getElementById('cart-count-footer');
            if(cartCountEl) {
                cartCountEl.style.transform = 'scale(1.3)';
                cartCountEl.style.transition = 'transform 0.2s ease';
                setTimeout(() => {
                    cartCountEl.style.transform = 'scale(1)';
                }, 200);
            }
        }

        function showNotification(message) {
            // Cek apakah sudah ada notifikasi
            let existingNotif = document.getElementById('cart-notification');
            if(existingNotif) {
                existingNotif.remove();
            }
            
            // Buat elemen notifikasi
            let notif = document.createElement('div');
            notif.id = 'cart-notification';
            notif.className = 'fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm font-medium';
            notif.style.animation = 'slideIn 0.3s ease';
            notif.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i>' + message;
            
            // Tambahkan ke DOM
            document.body.appendChild(notif);
            
            // Hapus setelah 2 detik
            setTimeout(() => {
                notif.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    notif.remove();
                }, 300);
            }, 2000);
        }

        // Inject CSS untuk animasi
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Jalankan saat DOM ready dan langsung saat script dimuat
        document.addEventListener('DOMContentLoaded', updateCartCount);
        updateCartCount();
    </script>
</body>
</html>