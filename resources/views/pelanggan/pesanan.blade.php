<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keranjang Pesanan - Restoran</title>
    
    {{-- Script Midtrans Snap (Sandbox) --}}
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            animation: fadeIn 0.35s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen page-transition">

    {{-- HEADER KELUAR / KEMBALI --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('pelanggan.menu', $meja->token) }}" class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-xs">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-base font-bold text-gray-900 leading-tight">Keranjang Pesanan</h1>
                    <p class="text-xs text-orange-600 font-medium">Meja: <span class="font-bold bg-orange-100 px-2 py-0.5 rounded-md text-orange-700">{{ $meja->nama_meja }}</span></p>
                </div>
            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA KERANJANG --}}
    <main class="max-w-md mx-auto px-4 py-5 pb-32">
        <div id="cart-items-container" class="space-y-3">
            {{-- Item dari localStorage akan muncul di sini --}}
        </div>

        {{-- Tampilan Jika Keranjang Kosong --}}
        <div id="empty-cart" class="hidden bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-xs my-6">
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base mb-1">Keranjang Masih Kosong</h3>
            <p class="text-xs text-gray-400 mb-5">Anda belum menambahkan menu apapun ke keranjang pesanan.</p>
            <a href="{{ route('pelanggan.menu', $meja->token) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-orange-600 transition">
                <i class="fa-solid fa-utensils"></i> Pilih Menu Sekarang
            </a>
        </div>
    </main>

    {{-- ================= STICKY FOOTER ================= --}}
    <div id="cart-summary" class="fixed inset-x-0 bottom-0 z-50">
        <div class="bg-white border-t border-gray-200 shadow-[0_-6px_20px_rgba(0,0,0,0.08)]">
            <div class="max-w-md mx-auto px-4 py-4">
                {{-- Ringkasan --}}
                <div class="bg-gray-50 border rounded-xl p-3 mb-3">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>
                            Total Item
                            (<span id="total-items">0</span> pesanan)
                        </span>
                        <span id="subtotal-harga" class="font-semibold">
                            Rp 0
                        </span>
                    </div>
                    <div class="border-t mt-2 pt-2 flex justify-between">
                        <span class="font-bold">
                            Total Pembayaran
                        </span>
                        <span id="total-bayar" class="font-bold text-orange-500 text-lg">
                            Rp 0
                        </span>
                    </div>
                </div>

                {{-- Tombol Checkout --}}
                <button type="button" onclick="kirimPesanan()" class="w-full h-12 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold transition flex items-center justify-center">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan & Bayar (QRIS)
                </button>
            </div>
        </div>
    </div>

    {{-- SCRIPT PENGELOLA KERANJANG & MIDTRANS --}}
    <script>
        const storageKey = 'cart_meja_{{ $meja->token }}';

        function getCart() {
            let data = localStorage.getItem(storageKey);
            return data ? JSON.parse(data) : [];
        }

        function saveCart(cart) {
            localStorage.setItem(storageKey, JSON.stringify(cart));
            renderCart();
        }

        function renderCart() {
            const cart = getCart();
            const container = document.getElementById('cart-items-container');
            const emptyState = document.getElementById('empty-cart');
            const summarySection = document.getElementById('cart-summary');

            if (!cart || cart.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                summarySection.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            summarySection.classList.remove('hidden');

            let html = '';
            let totalItems = 0;
            let subtotal = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.harga * item.qty;
                subtotal += itemTotal;
                totalItems += item.qty;

                html += `
                    <div class="bg-white border border-gray-100 rounded-2xl p-3.5 shadow-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="/uploads/menu/${item.gambar}" alt="${item.nama}" class="w-14 h-14 object-cover rounded-xl border border-gray-100">
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 leading-tight">${item.nama}</h4>
                                <p class="text-xs text-orange-600 font-extrabold mt-1">Rp ${formatRupiah(item.harga)}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                            <button type="button" onclick="changeQty(${index}, -1)" class="w-7 h-7 bg-white rounded-lg flex items-center justify-center text-gray-600 shadow-xs hover:bg-red-50 hover:text-red-500 transition text-xs">
                                <i class="fa-solid ${item.qty === 1 ? 'fa-trash text-red-500' : 'fa-minus'}"></i>
                            </button>
                            <span class="font-bold text-xs text-gray-800 w-4 text-center">${item.qty}</span>
                            <button type="button" onclick="changeQty(${index}, 1)" class="w-7 h-7 bg-white rounded-lg flex items-center justify-center text-gray-600 shadow-xs hover:bg-orange-50 hover:text-orange-500 transition text-xs">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            document.getElementById('total-items').innerText = totalItems;
            document.getElementById('subtotal-harga').innerText = 'Rp ' + formatRupiah(subtotal);
            document.getElementById('total-bayar').innerText = 'Rp ' + formatRupiah(subtotal);
        }

        function changeQty(index, delta) {
            let cart = getCart();
            cart[index].qty += delta;

            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }

            saveCart(cart);
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // Fungsi Tombol Kirim Pesanan yang Terhubung ke Midtrans
        async function kirimPesanan() {
            const cart = getCart();

            if (cart.length === 0) {
                alert('Keranjang pesanan masih kosong!');
                return;
            }

            const btnSubmit = document.querySelector('button[onclick="kirimPesanan()"]');
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menghubungkan ke Midtrans...';
            btnSubmit.disabled = true;

            try {
                let response = await fetch("{{ route('pesanan.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        token_meja: "{{ $meja->token }}",
                        pesanan: cart
                    })
                });

                let result = await response.json();

                if (response.ok && result.status === 'success') {
                    // JANGAN hapus keranjang di sini agar jika user batal, keranjang masih ada
                    
                    // REDIRECT PELANGGAN KE HALAMAN PEMBAYARAN MIDTRANS
                    window.location.href = result.payment_url;
                } else {
                    alert('Gagal membuat pesanan: ' + (result.message || 'Terjadi kesalahan'));
                    btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan & Bayar (QRIS)';
                    btnSubmit.disabled = false;
                }
            } catch (error) {
                alert('Terjadi kesalahan sistem.');
                console.error(error);
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan & Bayar (QRIS)';
                btnSubmit.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>