<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keranjang Pesanan</title>

    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            animation: fadeIn 0.35s ease-out forwards;
        }
        .note-area {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.3s ease;
        }
        .note-area.open {
            grid-template-rows: 1fr;
        }
        .note-area > div {
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen page-transition">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center gap-3">
            <a href="{{ route('pelanggan.menu', $meja->token) }}" class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-900 leading-tight">Keranjang Pesanan</h1>
                <p class="text-xs text-orange-600 font-medium">Meja: <span class="font-bold bg-orange-100 px-2 py-0.5 rounded-md text-orange-700">{{ $meja->nama_meja }}</span></p>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 py-5 pb-64 space-y-3">
        <div id="cart-items-container" class="space-y-3"></div>

        <div id="empty-cart" class="hidden bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm my-6">
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base mb-1">Keranjang Masih Kosong</h3>
            <p class="text-xs text-gray-400 mb-5">Anda belum menambahkan menu apapun.</p>
            <a href="{{ route('pelanggan.menu', $meja->token) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-orange-600 transition">
                <i class="fa-solid fa-utensils"></i> Pilih Menu Sekarang
            </a>
        </div>

        <div id="section-catatan-umum" class="hidden">
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
                <textarea id="catatan-umum" rows="3" maxlength="300" placeholder="Contoh: Tidak pakai bawang, level pedas sedang..." class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-300 resize-none focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"></textarea>
                <div class="flex justify-end mt-1.5">
                    <span id="char-count-umum" class="text-[10px] text-gray-300">0 / 300</span>
                </div>
            </div>
        </div>
    </main>

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
                <button type="button" id="btn-checkout" onclick="kirimPesanan()" class="w-full h-12 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold transition flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan & Bayar (QRIS)
                </button>
            </div>
        </div>
    </div>

    <script>
    const STORAGE_KEY = 'cart_meja_{{ $meja->token }}';
    const NOTE_STORAGE_KEY = 'note_meja_{{ $meja->token }}';

    function getCart() {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : [];
    }

    function saveCartRaw(cart) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    }

    function saveCart(cart) {
        saveCartRaw(cart);
        renderCart();
    }

    function saveCatatanItem(index, value) {
        const cart = getCart();
        if (cart[index] !== undefined) {
            cart[index].catatan_item = value.trim();
            saveCartRaw(cart);
        }
    }

    function renderCart() {
        const cart = getCart();
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('empty-cart');
        const summaryEl = document.getElementById('cart-summary');
        const catatanUmumSec = document.getElementById('section-catatan-umum');

        if (!cart || cart.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            summaryEl.classList.add('hidden');
            catatanUmumSec.classList.add('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        summaryEl.classList.remove('hidden');
        catatanUmumSec.classList.remove('hidden');

        let html = '';
        let totalItems = 0;
        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.harga * item.qty;
            const catatanVal = item.catatan_item || '';
            const hasNote = catatanVal.length > 0;
            subtotal += itemTotal;
            totalItems += item.qty;

            html += `
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between gap-3 p-3.5">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <img src="/uploads/menu/${escHtml(item.gambar)}" alt="${escHtml(item.nama)}" class="w-14 h-14 object-cover rounded-xl border-gray-100 shrink-0" onerror="this.src='/uploads/menu/default.png'">
                        <div class="min-w-0">
                            <h4 class="font-bold text-sm text-gray-900 leading-tight truncate">${escHtml(item.nama)}</h4>
                            <p class="text-xs text-orange-600 font-extrabold mt-0.5">Rp ${formatRupiah(item.harga)}</p>
                            <button type="button" id="btn-note-${index}" onclick="toggleNote(${index})" class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-semibold transition ${hasNote ? 'text-amber-600' : 'text-gray-400 hover:text-amber-500'}">
                                <i class="fa-solid fa-note-sticky text-[9px]"></i>
                                ${hasNote ? 'Lihat / edit catatan' : '+ Tambah catatan'}
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl p-1 shrink-0">
                        <button type="button" onclick="changeQty(${index}, -1)" class="w-7 h-7 bg-white rounded-lg flex items-center justify-center text-gray-600 shadow-sm hover:bg-red-50 hover:text-red-500 transition text-xs">
                            <i class="fa-solid ${item.qty === 1 ? 'fa-trash text-red-400' : 'fa-minus'}"></i>
                        </button>
                        <span class="font-bold text-xs text-gray-800 w-5 text-center">${item.qty}</span>
                        <button type="button" onclick="changeQty(${index}, 1)" class="w-7 h-7 bg-white rounded-lg flex items-center justify-center text-gray-600 shadow-sm hover:bg-orange-50 hover:text-orange-500 transition text-xs">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div id="note-area-${index}" class="note-area ${hasNote ? 'open' : ''}">
                    <div>
                        <div class="px-3.5 pb-3.5">
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                                <div class="flex items-center gap-1.5 mb-2">
                                    <i class="fa-solid fa-note-sticky text-amber-500 text-[10px]"></i>
                                    <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wide">Catatan untuk item ini</span>
                                </div>
                                <textarea id="note-input-${index}" rows="2" maxlength="150" placeholder="Contoh: Tidak pakai sambal..." onkeyup="onNoteInput(${index}, this.value)" onchange="onNoteInput(${index}, this.value)" class="w-full text-xs text-gray-700 placeholder-amber-300 bg-transparent resize-none focus:outline-none leading-relaxed">${escHtml(catatanVal)}</textarea>
                                <div class="flex items-center justify-between mt-1.5 pt-1.5 border-t border-amber-200">
                                    <button type="button" onclick="hapusCatatanItem(${index})" class="inline-flex items-center gap-1 text-[10px] text-red-400 hover:text-red-600 transition">
                                        <i class="fa-solid fa-trash-can"></i> Hapus catatan
                                    </button>
                                    <span id="char-count-item-${index}" class="text-[10px] text-amber-400">${catatanVal.length} / 150</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('total-items').innerText = totalItems;
        document.getElementById('subtotal-harga').innerText = 'Rp ' + formatRupiah(subtotal);
        document.getElementById('total-bayar').innerText = 'Rp ' + formatRupiah(subtotal);

        const savedNote = localStorage.getItem(NOTE_STORAGE_KEY) || '';
        const catatanUmumEl = document.getElementById('catatan-umum');
        if (catatanUmumEl && savedNote) {
            catatanUmumEl.value = savedNote;
            updateCharCountUmum(savedNote.length);
        }
    }

    function toggleNote(index) {
        const area = document.getElementById(`note-area-${index}`);
        const isOpen = area.classList.contains('open');
        if (isOpen) {
            area.classList.remove('open');
        } else {
            area.classList.add('open');
            setTimeout(() => {
                const input = document.getElementById(`note-input-${index}`);
                if (input) input.focus();
            }, 320);
        }
    }

    function onNoteInput(index, value) {
        saveCatatanItem(index, value);
        const counter = document.getElementById(`char-count-item-${index}`);
        if (counter) counter.innerText = `${value.length} / 150`;
        updateNoteBtnLabel(index, value);
    }

    function updateNoteBtnLabel(index, value) {
        const btn = document.getElementById(`btn-note-${index}`);
        if (!btn) return;
        if (value.trim().length > 0) {
            btn.className = 'mt-1.5 inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 transition';
            btn.innerHTML = '<i class="fa-solid fa-note-sticky text-[9px]"></i> Lihat / edit catatan';
        } else {
            btn.className = 'mt-1.5 inline-flex items-center gap-1 text-[10px] font-semibold text-gray-400 hover:text-amber-500 transition';
            btn.innerHTML = '<i class="fa-solid fa-note-sticky text-[9px]"></i> + Tambah catatan';
        }
    }

    function hapusCatatanItem(index) {
        saveCatatanItem(index, '');
        const input = document.getElementById(`note-input-${index}`);
        if (input) input.value = '';
        const counter = document.getElementById(`char-count-item-${index}`);
        if (counter) counter.innerText = '0 / 150';
        updateNoteBtnLabel(index, '');
        const area = document.getElementById(`note-area-${index}`);
        if (area) area.classList.remove('open');
    }

    function updateCharCountUmum(len) {
        const el = document.getElementById('char-count-umum');
        if (el) el.innerText = `${len} / 300`;
    }

    function changeQty(index, delta) {
        const cart = getCart();
        if (!cart[index]) return;
        cart[index].qty += delta;
        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }
        saveCart(cart);
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    async function kirimPesanan() {
        const cart = getCart();
        if (cart.length === 0) {
            alert('Keranjang pesanan masih kosong!');
            return;
        }

        const catatanUmum = (document.getElementById('catatan-umum')?.value || '').trim();
        const btn = document.getElementById('btn-checkout');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menghubungkan ke Midtrans...';
        btn.disabled = true;

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
                localStorage.removeItem(NOTE_STORAGE_KEY);
                window.location.href = result.payment_url;
            } else {
                alert('Gagal membuat pesanan: ' + (result.message || 'Terjadi kesalahan.'));
                resetBtn();
            }
        } catch (error) {
            alert('Terjadi kesalahan sistem.');
            console.error(error);
            resetBtn();
        }
    }

    function resetBtn() {
        const btn = document.getElementById('btn-checkout');
        btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan & Bayar (QRIS)';
        btn.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderCart();
        const catatanUmumEl = document.getElementById('catatan-umum');
        if (catatanUmumEl) {
            catatanUmumEl.addEventListener('input', function() {
                localStorage.setItem(NOTE_STORAGE_KEY, this.value);
                updateCharCountUmum(this.value.length);
            });
        }
    });
    </script>
</body>
</html>