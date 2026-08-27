<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Restoran')</title>

    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Yeseva+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        yeseva: ['"Yeseva One"', 'serif'],
                    },
                    keyframes: {
                        slideIn: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideOut: {
                            '0%': { transform: 'translateY(0)', opacity: '1' },
                            '100%': { transform: 'translateY(-30px)', opacity: '0' },
                        },
                        badgePop: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.35)' },
                        }
                    },
                    animation: {
                        'slide-in': 'slideIn 0.35s ease-out forwards',
                        'slide-out': 'slideOut 0.25s ease-in forwards',
                        'badge-pop': 'badgePop 0.3s ease-out',
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased font-sans">

    <!-- HEADER: Sticky di atas untuk akses cepat ke keranjang -->
    <header class="sticky top-0 z-50 bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-xl">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between gap-2">
            
            <div class="flex items-center gap-2 md:gap-3 min-w-0">
                <div class="w-8 h-8 md:w-9 md:h-9 shrink-0 bg-white/20 backdrop-blur-md rounded-xl md:rounded-2xl flex items-center justify-center shadow-inner">
                    <i class="fa-solid fa-utensils text-sm md:text-lg"></i>
                </div>
                <h1 class="font-yeseva text-lg md:text-2xl tracking-tight truncate">
                    Warung Makan Haji Wiwin
                </h1>
            </div>

            <!-- BAGIAN YANG DIPERBAIKI: Hapus 'hidden sm:flex' agar tampil di HP -->
            <div class="flex items-center gap-3 md:gap-6 shrink-0">
                <div class="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
                    <i class="fa-solid fa-location-dot text-orange-200"></i>
                    <span class="font-semibold flex items-center gap-1">
                        <!-- Tulisan 'Meja:' disembunyikan di HP agar hemat ruang -->
                        <span class="hidden sm:inline">Meja:</span> 
                        <span class="bg-white/20 px-2 py-1 md:px-3 rounded-full text-[10px] md:text-xs whitespace-nowrap">
                            {{ $meja->nama_meja ?? '?' }}
                        </span>
                    </span>
                </div>
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT: pb-24 mencegah konten tertutup Bottom Nav -->
    <main class="max-w-4xl mx-auto px-4 py-6 pb-24">
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION: Navigasi utama untuk tampilan mobile -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="max-w-4xl mx-auto px-4 py-3 grid grid-cols-3">
            
            <a href="{{ route('pelanggan.menu', $meja->token) }}" class="relative flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('pelanggan.menu') ? 'text-orange-500' : 'text-gray-500 hover:text-orange-500' }}">
                <i class="fa-solid fa-book-open text-xl"></i>
                <span class="text-[10px] font-semibold">Menu</span>
                @if(request()->routeIs('pelanggan.menu'))
                    <span class="absolute bottom-1 w-6 h-0.5 bg-orange-500 rounded-full"></span>
                @endif
            </a>

            <a href="{{ route('pelanggan.pesanan', $meja->token) }}" class="relative flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('pelanggan.pesanan') ? 'text-orange-500' : 'text-gray-500 hover:text-orange-500' }}">
                <div class="relative">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span id="cart-count-footer" class="absolute -top-2 -right-3 hidden items-center justify-center w-5 h-5 rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm">
                        0
                    </span>
                </div>
                <span class="text-[10px] font-semibold">Keranjang</span>
                @if(request()->routeIs('pelanggan.pesanan'))
                    <span class="absolute bottom-1 w-6 h-0.5 bg-orange-500 rounded-full"></span>
                @endif
            </a>

            <a href="{{ route('pelanggan.histori', $meja->token) }}" class="relative flex flex-col items-center justify-center gap-1 py-1 transition-colors {{ request()->routeIs('pelanggan.histori') ? 'text-orange-500' : 'text-gray-500 hover:text-orange-500' }}">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                <span class="text-[10px] font-semibold">Histori</span>
                @if(request()->routeIs('pelanggan.histori'))
                    <span class="absolute bottom-1 w-6 h-0.5 bg-orange-500 rounded-full"></span>
                @endif
            </a>

        </div>
    </nav>

    <!-- TOAST CONTAINER -->
    <div id="toast-container" class="fixed top-24 left-1/2 -translate-x-1/2 z-[9999] w-[92%] max-w-sm pointer-events-none"></div>

    <script>
        const CART_KEY = 'cart_meja_{{ $meja->token ?? "default" }}';

        function getCart() {
            try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } 
            catch { return []; }
        }

        function updateCartCount() {
            const totalQty = getCart().reduce((total, item) => total + Number(item.qty || 0), 0);
            ['cart-count-header', 'cart-count-footer'].forEach(id => {
                const badge = document.getElementById(id);
                if (badge) {
                    badge.textContent = totalQty;
                    badge.classList.toggle('hidden', totalQty === 0);
                    badge.classList.toggle('flex', totalQty > 0);
                }
            });
        }

        function animateCartBadge() {
            const badge = document.getElementById('cart-count-footer');
            if (!badge) return;
            
            badge.classList.remove('animate-badge-pop');
            void badge.offsetWidth; 
            badge.classList.add('animate-badge-pop');
        }

        function showNotification(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const styles = {
                success: { bg: 'bg-emerald-500', icon: 'fa-check-circle' },
                error: { bg: 'bg-red-500', icon: 'fa-circle-xmark' },
                info: { bg: 'bg-blue-500', icon: 'fa-circle-info' }
            }[type] || { bg: 'bg-emerald-500', icon: 'fa-check-circle' };

            const toast = document.createElement('div');
            toast.className = `animate-slide-in pointer-events-auto flex items-center gap-3 ${styles.bg} text-white px-4 py-3 rounded-2xl shadow-xl mb-2 border border-white/10`;
            toast.innerHTML = `
                <div class="w-8 h-8 shrink-0 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid ${styles.icon} text-sm"></i>
                </div>
                <span class="text-xs font-medium">${message}</span>
            `;

            container.prepend(toast);

            setTimeout(() => {
                toast.classList.replace('animate-slide-in', 'animate-slide-out');
                setTimeout(() => toast.remove(), 280);
            }, 2500);
        }

        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>