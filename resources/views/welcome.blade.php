<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col justify-between font-sans antialiased">
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
            <div class="flex items-center gap-2 font-bold text-xl">
                <span class="text-red-600">UKK</span> App
            </div>
            @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('admin.login') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                            Log in
                        </a>
                    @endauth
                </nav>
            @endif
        </header>

        <main class="max-w-4xl mx-auto px-6 py-12 text-center my-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 sm:p-12 shadow-sm">
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Welcome to UKK Application</h1>
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg mb-8 max-w-xl mx-auto">
                    Manage your application efficiently with our streamlined admin dashboard and robust tools.
                </p>
                <div class="flex justify-center gap-4">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition shadow-md">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('admin.login') }}" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition shadow-md">
                            Login as Admin
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <footer class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
            Laravel v{{ app()->version() }} &bull; Built with Tailwind CSS
        </footer>
    </body>
</html>
