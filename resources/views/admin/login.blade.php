<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Login</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans">

    {{-- Container utama agar card berada di tengah --}}
    <div class="min-h-screen flex items-center justify-center px-4">

        {{-- Card Login --}}
        <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-8">

            {{-- Judul --}}
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">
                Dashboard Login
            </h1>

            {{-- ================= NOTIFIKASI ERROR ================= --}}
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>Login Gagal!</span>
                </div>
                <ul class="list-disc list-inside text-sm ml-1 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ================= NOTIFIKASI SUKSES (Misal dari Logout) ================= --}}
            @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            {{-- Form Login --}}
            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf

                {{-- ================= Username ================= --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold mb-2">Username</label>
                    <div class="relative">

                        {{-- Ikon Library FontAwesome untuk User --}}
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-user"></i>
                        </span>

                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan Username" required
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    </div>
                </div>

                {{-- ================= Password ================= --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2">Password</label>
                    <div class="relative">

                        {{-- Ikon Library FontAwesome untuk Gembok/Lock --}}
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <input id="password" type="password" name="password" placeholder="••••••••" required
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-12 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none">

                        {{-- Tombol Show / Hide Password pakai Ikon Mata FontAwesome --}}
                        <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-orange-500">
                            <i id="eyeIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Tombol Login --}}
                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-sm">
                    Masuk
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-6 text-center text-gray-400 text-sm">
                Assalamualaikum
            </div>

        </div>
    </div>

    <script>
        // Script untuk menampilkan/menyembunyikan password sekaligus mengganti ikon mata FontAwesome
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (password.type === "password") {
                password.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash"); // Berubah jadi mata dicoret
            } else {
                password.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye"); // Kembali ke mata normal
            }
        }
    </script>

</body>