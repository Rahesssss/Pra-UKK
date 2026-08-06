<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Pesanan - Restoran</title>
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .page-transition {
            animation: fadeIn 0.35s ease-out forwards;
        }
    </style>

</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans pb-48 page-transition">

    {{-- HEADER KELUAR / KEMBALI --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Tombol Kembali ke Menu (Otomatis kembali atau ke route menu) --}}
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
    <main class="max-w-md mx-auto px-4 py-5">
        
        {{-- Container List Item Pesanan (Diisi otomatis oleh JS) --}}
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

    {{-- STICKY FOOTER: RINGKASAN PEMBAYARAN & TOMBOL KIRIM (MENEMPEL DI BAWAH) --}}
    <div id="cart-summary" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] z-50">
        <div class="max-w-md mx-auto space-y-3">
            
            {{-- Rincian Biaya Ringkas --}}
            <div class="space-y-1.5 text-xs bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="flex justify-between text-gray-600">
                    <span>Total Item (<span id="total-items">0</span> pesanan)</span>
                    <span id="subtotal-harga" class="font-semibold text-gray-800">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm font-extrabold text-gray-900 pt-1 border-t border-gray-200">
                    <span>Total Pembayaran</span>
                    <span id="total-bayar" class="text-orange-600">Rp 0</span>
                </div>
            </div>

            {{-- Tombol Submit --}}
            <button type="button" onclick="kirimPesanan()" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Ke Kasir
            </button>
        </div>
    </div>

    {{-- SCRIPT PENGELOLA KERANJANG (LOCALSTORAGE) --}}
    <script>
        const storageKey = 'cart_meja_{{ $meja->token }}';

        function getCart() {
            return JSON.parse(localStorage.getItem(storageKey)) || [];
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

            if (cart.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                summarySection.classList.add('hidden'); // Sembunyikan sticky footer jika kosong
                return;
            }

            emptyState.classList.add('hidden');
            summarySection.classList.remove('hidden'); // Tampilkan sticky footer

            let html = '';
            let totalItems = 0;
            let subtotal = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.harga * item.qty;
                subtotal += itemTotal;
                totalItems += item.qty;

                html += `
                    <div class="bg-white border border-gray-100 rounded-2xl p-3.5 shadow-xs flex items-center justify-between gap-3">
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-gray-900 leading-tight">${item.nama}</h4>
                            <p class="text-xs text-orange-600 font-extrabold mt-1">Rp ${formatRupiah(item.harga)}</p>
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

        function kirimPesanan() {
            const cart = getCart();

            if (cart.length === 0) {
                alert('Keranjang pesanan masih kosong!');
                return;
            }

            // Simulasi pengiriman pesanan (bisa diintegrasikan ke database lewat AJAX/POST nantinya)
            alert('Terima kasih! Pesanan untuk Meja {{ $meja->nama_meja }} berhasil dikirim ke kasir.');
            
            // Bersihkan keranjang meja setelah dipesan
            localStorage.removeItem(storageKey);
            renderCart();
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>