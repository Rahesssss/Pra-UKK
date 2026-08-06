<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Resto')</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Animasi Dropdown --}}
    <style>
        .dropdown-animate {
            animation: dropFadeIn 0.2s ease-out forwards;
        }
        @keyframes dropFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800 flex min-h-screen font-sans antialiased relative">

    {{-- Backdrop Overlay untuk Sidebar Drawer di Mobile/Tablet --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/40 hidden lg:hidden"></div>

    {{-- ================= SIDEBAR ================= --}}
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col shrink-0 py-5 transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static lg:w-56">
        
        {{-- Sidebar Header (hanya muncul di Mobile/Tablet Drawer) --}}
        <div class="flex items-center justify-between px-6 pb-4 border-b border-gray-100 lg:hidden">
            <span class="font-bold text-lg text-gray-800">Admin Resto</span>
            <button id="btn-close-sidebar" class="text-gray-500 hover:text-orange-500 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 flex flex-col gap-1">
            {{-- Menu Orders --}}
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span>Orders</span>
            </a>

            {{-- Menu Makanan/Minuman --}}
            <a href="{{ route('admin.menu.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold transition-colors {{ request()->routeIs('admin.menu.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                <i class="fa-solid fa-burger w-5 text-center"></i>
                <span>Menu</span>
            </a>

            <a href="{{ route('admin.meja.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold transition-colors {{ request()->routeIs('admin.meja.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                <i class="fa-solid fa-table-cells-large w-5 text-center"></i>
                <span>Meja</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-500 font-medium hover:bg-orange-50 hover:text-orange-600 transition-colors">
                <i class="fa-solid fa-file-lines w-5 text-center"></i>
                <span>Laporan</span>
            </a>
        </nav>
    </aside>

    {{-- ================= MAIN WRAPPER ================= --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- TOP BAR --}}
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <button id="btn-toggle-sidebar" class="text-gray-500 hover:text-orange-500 transition p-1 lg:hidden">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800">@yield('header-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-4">

                <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                    <i class="fa-solid fa-user"></i>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-500 px-3 py-1.5 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    {{-- Sidebar Toggle JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
            const btnCloseSidebar = document.getElementById('btn-close-sidebar');
            const sidebar = document.getElementById('admin-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }

            if (btnToggleSidebar) btnToggleSidebar.addEventListener('click', toggleSidebar);
            if (btnCloseSidebar) btnCloseSidebar.addEventListener('click', toggleSidebar);
            if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>

</html>