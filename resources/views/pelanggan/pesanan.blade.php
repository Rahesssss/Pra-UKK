<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keranjang Pesanan</title>

    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        body { animation: fadeIn 0.35s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-50 text-gray-800 font-sans min-h-screen pb-64">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center gap-3">
            <a href="{{ route('pelanggan.menu', $meja->token) }}"
               class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center
                      text-gray-600 hover:bg-gray-50 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-900 leading-tight">Keranjang Pesanan</h1>
                <p class="text-xs text-orange-600 font-medium">
                    Meja: <span class="font-bold bg-orange-100 px-2 py-0.5 rounded-md text-orange-700">{{ $meja->nama_meja }}</span>
                </p>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="max-w-md mx-auto px-4 py-5 space-y-3">

        {{-- Cart Items --}}
        <div id="cart-items-container" class="space-y-3"></div>

        {{-- Empty Cart --}}
        <div id="empty-cart" class="hidden bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm my-6">
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base mb-1">Keranjang Masih Kosong</h3>
            <p class="text-xs text-gray-400 mb-5">Anda belum menambahkan menu apapun.</p>
            <a href="{{ route('pelanggan.menu', $meja->token) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-orange-600 transition">
                <i class="fa-solid fa-utensils"></i> Pilih Menu Sekarang
            </a>
        </div>

        {{-- Catatan Umum --}}
        <div id="section-catatan-umum" class="hidden mt-4">
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-note-sticky text-amber-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-gray-800">Catatan untuk Dapur</h3>
                        <p class="text-[10px] text-gray-400">Opsional · Alergi, permintaan khusus, dll.</p>
                    </div>
                </div>
                <textarea id="catatan-umum" rows="3" maxlength="300"
                          placeholder="Contoh: Tidak pakai bawang, level pedas sedang..."
                          class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-700
                                 placeholder-gray-300 resize-none focus:outline-none focus:ring-2
                                 focus:ring-amber-400 focus:border-amber-400 transition"></textarea>
                <div class="flex justify-end mt-1.5">
                    <span id="char-count-umum" class="text-[10px] text-gray-300">0 / 300</span>
                </div>
            </div>
        </div>
    </main>

    {{-- Checkout Bar (Stick Bottom) --}}
    <div id="cart-summary" class="fixed inset-x-0 bottom-0 z-50 hidden">
        <div class="bg-white border-t border-gray-200 shadow-[0_-6px_20px_rgba(0,0,0,0.08)]">
            <div class="max-w-md mx-auto px-4 py-4 space-y-3">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <div class="flex justify-between text-sm text-gray-600 mb-1.5">
                        <span>Total Item (<span id="total-items">0</span> pesanan)</span>
                        <span id="subtotal-harga" class="font-semibold">Rp 0</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 flex justify-between items-center">
                        <span class="font-bold text-gray-900 text-sm">Total Pembayaran</span>
                        <span id="total-bayar" class="font-bold text-orange-500 text-lg">Rp 0</span>
                    </div>
                </div>
                <button type="button" id="btn-checkout" onclick="kirimPesanan()"
                        class="w-full h-12 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold
                               transition flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan & Bayar (QRIS)
                </button>
            </div>
        </div>
    </div>

    <script>
        const STORAGE_KEY = 'cart_meja_{{ $meja->token }}';
        const NOTE_KEY    = 'note_meja_{{ $meja->token }}';

        function getCart() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch { return []; }
        }

        function saveCart(cart) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
            renderCart();
        }

        function renderCart() {
            const cart      = getCart();
            const container = document.getElementById('cart-items-container');
            const empty     = document.getElementById('empty-cart');
            const summary   = document.getElementById('cart-summary');
            const noteSection = document.getElementById('section-catatan-umum');

            if (cart.length === 0) {
                container.innerHTML = '';
                empty.classList.remove('hidden');
                summary.classList.add('hidden');
                noteSection.classList.add('hidden');
                return;
            }

            empty.classList.add('hidden');
            summary.classList.remove('hidden');
            noteSection.classList.remove('hidden');

            let html       = '';
            let totalItems = 0;
            let subtotal   = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.harga * item.qty;
                subtotal   += itemTotal;
                totalItems += item.qty;

                html += `
                <div class="bg-white border border-gray-100 rounded-2xl p-3.5 flex items-center gap-3 shadow-sm">
                    <img src="/uploads/menu/${esc(item.gambar)}" alt="${esc(item.nama)}"
                         class="w-16 h-16 object-cover rounded-xl border border-gray-50 shrink-0"
                         onerror="this.src='/uploads/menu/default.png'">

                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm text-gray-900 truncate mb-1">${esc(item.nama)}</h4>
                        <div class="flex flex-col">
                            <span class="text-[11px] text-gray-500 font-medium">
                                Rp ${formatRp(item.harga)} <span class="text-[10px] mx-0.5">x</span> ${item.qty}
                            </span>
                            <span class="text-sm text-orange-600 font-extrabold mt-0.5">
                                Rp ${formatRp(itemTotal)}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl p-1 shrink-0">
                        <button onclick="changeQty(${index}, -1)"
                                class="w-7 h-7 bg-white rounded-lg flex items-center justify-center
                                       text-gray-600 shadow-sm hover:bg-red-50 hover:text-red-500 transition">
                            <i class="fa-solid ${item.qty === 1 ? 'fa-trash-can text-red-400' : 'fa-minus'} text-xs"></i>
                        </button>
                        <span class="font-bold text-xs text-gray-800 w-5 text-center">${item.qty}</span>
                        <button onclick="changeQty(${index}, 1)"
                                class="w-7 h-7 bg-white rounded-lg flex items-center justify-center
                                       text-gray-600 shadow-sm hover:bg-orange-50 hover:text-orange-500 transition">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
            document.getElementById('total-items').textContent    = totalItems;
            document.getElementById('subtotal-harga').textContent = 'Rp ' + formatRp(subtotal);
            document.getElementById('total-bayar').textContent    = 'Rp ' + formatRp(subtotal);

            // Restore catatan umum
            const savedNote = localStorage.getItem(NOTE_KEY) || '';
            const noteEl    = document.getElementById('catatan-umum');
            if (noteEl && savedNote) {
                noteEl.value = savedNote;
                updateCharCount(savedNote.length);
            }
        }

        function changeQty(index, delta) {
            const cart = getCart();
            if (!cart[index]) return;
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            saveCart(cart);
        }

        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }

        function esc(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m =>
                ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[m])
            );
        }

        function updateCharCount(len) {
            const el = document.getElementById('char-count-umum');
            if (el) el.textContent = `${len} / 300`;
        }

        async function kirimPesanan() {
            const cart = getCart();
            if (cart.length === 0) {
                alert('Keranjang pesanan masih kosong!');
                return;
            }

            const catatanUmum = (document.getElementById('catatan-umum')?.value || '').trim();
            const btn = document.getElementById('btn-checkout');
            const originalHTML = btn.innerHTML;

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghubungkan ke Midtrans...';
            btn.disabled  = true;

            try {
                const response = await fetch("{{ route('pesanan.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        token_meja: '{{ $meja->token }}',
                        pesanan: cart,
                        catatan_umum: catatanUmum,
                    }),
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    localStorage.removeItem(NOTE_KEY);
                    window.location.href = result.payment_url;
                } else {
                    alert('Gagal membuat pesanan: ' + (result.message || 'Terjadi kesalahan.'));
                    resetBtn(originalHTML);
                }
            } catch (error) {
                alert('Terjadi kesalahan sistem.');
                console.error(error);
                resetBtn(originalHTML);
            }
        }

        function resetBtn(html) {
            const btn = document.getElementById('btn-checkout');
            btn.innerHTML = html;
            btn.disabled  = false;
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();

            const noteEl = document.getElementById('catatan-umum');
            if (noteEl) {
                noteEl.addEventListener('input', function () {
                    localStorage.setItem(NOTE_KEY, this.value);
                    updateCharCount(this.value.length);
                });
            }
        });
    </script>
</body>
</html>
