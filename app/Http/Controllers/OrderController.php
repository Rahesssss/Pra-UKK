<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Detail_Pesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    // Buat snap token dan simpan data pesanan ke cache (belum ke database)
    public function store(Request $request)
    {
        $request->validate([
            'token_meja'      => 'required|string',
            'pesanan'         => 'required|array|min:1',
            'pesanan.*.id'    => 'required',
            'pesanan.*.nama'  => 'required|string',
            'pesanan.*.harga' => 'required|numeric|min:1',
            'pesanan.*.qty'   => 'required|integer|min:1',
        ]);

        $meja = Meja::where('token', $request->token_meja)->firstOrFail();
        $cart = $request->pesanan;
        $catatanUmum = trim($request->input('catatan_umum', ''));

        $total_harga = collect($cart)->sum(fn($i) => $i['harga'] * $i['qty']);

        try {
            $order_id = 'ORD-' . time() . '-' . rand(100, 999);

            $item_details = array_map(fn($i) => [
                'id'       => $i['id'],
                'price'    => (int) $i['harga'],
                'quantity' => (int) $i['qty'],
                'name'     => substr($i['nama'], 0, 50),
            ], $cart);

            // Gunakan getSnapToken untuk popup, bukan redirect
            $snapToken = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id'     => $order_id,
                    'gross_amount' => (int) $total_harga,
                ],
                'item_details'    => $item_details,
                'customer_details' => [
                    'first_name' => 'Meja: ' . $meja->nama_meja,
                ],
            ]);

            // Simpan ke cache, baru masuk database setelah pembayaran berhasil
            Cache::put('order_data_' . $order_id, [
                'meja_id'      => $meja->id_meja,
                'cart'         => $cart,
                'total_harga'  => $total_harga,
                'session_id'   => session()->getId(),
                'catatan_umum' => $catatanUmum,
            ], now()->addHours(2));

            return response()->json([
                'status'     => 'success',
                'snap_token' => $snapToken,
                'order_id'   => $order_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans store error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Webhook dari Midtrans server
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512",
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        if ($hashed !== $request->signature_key) {
            return response()->json(['status' => 'error'], 403);
        }

        $status   = $request->transaction_status;
        $order_id = $request->order_id;

        // Hanya simpan ke database jika pembayaran berhasil
        if (in_array($status, ['settlement', 'capture'])) {
            $this->simpanDariCacheJikaBelumAda($order_id, 'callback');
        }

        // Pembayaran gagal/batal, hapus cache saja
        if (in_array($status, ['cancel', 'expire', 'deny'])) {
            Cache::forget('order_data_' . $order_id);
        }

        return response()->json(['status' => 'success']);
    }

    // Dipanggil via AJAX dari halaman keranjang setelah snap.pay onSuccess
    public function sukses(Request $request, $token)
    {
        $meja     = Meja::where('token', $token)->firstOrFail();
        $order_id = $request->query('order_id');

        if (!$order_id) {
            return response()->json(['status' => 'error', 'message' => 'Order ID tidak ada.'], 400);
        }

        // Verifikasi status pembayaran langsung ke Midtrans
        try {
            $midtrans = Transaction::status($order_id);
            $txStatus = $midtrans->transaction_status ?? null;
            $fraud    = $midtrans->fraud_status ?? null;

            // Belum berhasil, jangan simpan
            if (!in_array($txStatus, ['capture', 'settlement'])) {
                Cache::forget('order_data_' . $order_id);
                return response()->json(['status' => 'error', 'message' => 'Pembayaran belum berhasil.'], 400);
            }

            // Fraud challenge, jangan simpan
            if ($txStatus === 'capture' && $fraud === 'challenge') {
                return response()->json(['status' => 'error', 'message' => 'Pembayaran sedang direview.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal verifikasi pembayaran.'], 500);
        }

        // Pembayaran valid, simpan ke database
        $saved = $this->simpanDariCacheJikaBelumAda($order_id, 'sukses');

        if ($saved === false) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan pesanan.'], 500);
        }

        return response()->json([
            'status'       => 'success',
            'redirect_url' => route('pelanggan.histori', ['token' => $token]),
        ]);
    }

    // Simpan dari cache ke database, cek duplikat, return true/false/null
    private function simpanDariCacheJikaBelumAda(string $order_id, string $source): ?bool
    {
        $cached = Cache::get('order_data_' . $order_id);
        if (!$cached) return null;

        // Cek duplikat berdasarkan session, meja, total, dan status
        $exists = Pesanan::where('session_id', $cached['session_id'])
            ->where('meja_id', $cached['meja_id'])
            ->where('total_harga', $cached['total_harga'])
            ->where('status_pembayaran', 'Lunas')
            ->exists();

        if ($exists) {
            Cache::forget('order_data_' . $order_id);
            return true;
        }

        DB::beginTransaction();
        try {
            $this->simpanPesananDariCache($cached);
            DB::commit();
            Cache::forget('order_data_' . $order_id);
            Log::info("Order saved from {$source}: {$order_id}");
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Save error ({$source}): " . $e->getMessage());
            return false;
        }
    }

    private function simpanPesananDariCache(array $data): Pesanan
    {
        $order = new Pesanan();
        $order->meja_id           = $data['meja_id'];
        $order->total_harga       = $data['total_harga'];
        $order->session_id        = $data['session_id'];
        $order->status_pembayaran = 'Lunas';
        $order->status_pesanan    = 'Menunggu';
        $order->catatan           = $data['catatan_umum'] ?? null;
        $order->save();

        foreach ($data['cart'] as $item) {
            $detail = new Detail_Pesanan();
            $detail->pesanan_id = $order->id;
            $detail->menu_id    = $item['id'];
            $detail->jumlah     = $item['qty'];
            $detail->harga      = $item['harga'];
            $detail->total      = $item['harga'] * $item['qty'];
            $detail->save();
        }

        return $order;
    }

    public function histori($token)
    {
        $meja = Meja::where('token', $token)->firstOrFail();

        $orders = Pesanan::with('detailPesanan.menu')
            ->where('meja_id', $meja->id_meja)
            ->where('session_id', session()->getId())
            ->where('status_pembayaran', 'Lunas')
            ->orderBy('created_at', 'desc')
            ->get();

        $nomorUrut = $this->getNomorUrutHarian($orders);

        return view('pelanggan.histori', compact('meja', 'orders', 'nomorUrut'));
    }

    private function getNomorUrutHarian(iterable $orders): array
    {
        $tanggalUnik = collect($orders)
            ->pluck('created_at')
            ->map(fn($d) => $d->toDateString())
            ->unique()->values();

        $nomorUrut = [];
        foreach ($tanggalUnik as $tanggal) {
            $ids = Pesanan::whereDate('created_at', $tanggal)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id');

            foreach ($ids as $i => $id) {
                $nomorUrut[$id] = $i + 1;
            }
        }
        return $nomorUrut;
    }

    public function showDetail($id)
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu'])->findOrFail($id);
        return view('admin.detail-pesanan', compact('pesanan'));
    }
}
