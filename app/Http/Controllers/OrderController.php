<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Detail_Pesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Step 1: Menerima data keranjang, simpan di Cache, dan berikan link Midtrans.
     * Tidak ada data yang masuk ke database di tahap ini.
     */
    public function store(Request $request)
    {
        $token_meja = $request->token_meja;
        $cart = $request->pesanan; // Array berisi id, nama, harga, qty

        $meja = Meja::where('token', $token_meja)->firstOrFail();

        // Hitung total harga
        $total_harga = 0;
        foreach ($cart as $item) {
            $total_harga += ($item['harga'] * $item['qty']);
        }

        try {
            // Konfigurasi Midtrans
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Buat Order ID unik untuk Midtrans
            $order_id = 'ORD-' . time() . '-' . rand(100, 999);

            // Format item untuk Midtrans
            $item_details = [];
            foreach ($cart as $item) {
                $item_details[] = [
                    'id' => $item['id'],
                    'price' => $item['harga'],
                    'quantity' => $item['qty'],
                    'name' => substr($item['nama'], 0, 50),
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => $total_harga,
                ],
                'item_details' => $item_details,
                'customer_details' => [
                    'first_name' => 'Meja: ' . $meja->nama_meja,
                ],
                'callbacks' => [
                    'finish' => route('pesanan.sukses', ['token' => $token_meja]),
                ]
            ];

            // Dapatkan Redirect URL dari Midtrans
            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            // SIMPAN DATA PESANAN DI CACHE (Bukan Database)
            // Cache bertahan 2 jam
            Cache::put('order_data_' . $order_id, [
                'meja_id' => $meja->id_meja,
                'cart' => $cart,
                'total_harga' => $total_harga,
                'session_id' => session()->getId()
            ], now()->addHours(2));

            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Webhook dari Midtrans. 
     * HANYA di sini data dimasukkan ke database jika pembayaran berhasil.
     */
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        // Verifikasi Signature
        if ($hashed !== $request->signature_key) {
            Log::error('Midtrans Callback: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $order_id = $request->order_id;

        // Cek jika status pembayaran sukses (settlement atau capture)
        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            
            // Ambil data dari cache
            $cachedData = Cache::get('order_data_' . $order_id);

            if ($cachedData) {
                DB::beginTransaction();
                try {
                    // 1. Insert ke tabel pesanan
                    $order = new Pesanan();
                    $order->meja_id = $cachedData['meja_id'];
                    $order->total_harga = $cachedData['total_harga'];
                    $order->session_id = $cachedData['session_id'];
                    $order->status_pembayaran = 'Lunas';
                    $order->status_pesanan = 'Menunggu';
                    $order->save();

                    // 2. Insert detail pesanan
                    foreach ($cachedData['cart'] as $item) {
                        $detail = new Detail_Pesanan();
                        $detail->pesanan_id = $order->id;
                        $detail->menu_id = $item['id'];
                        $detail->jumlah = $item['qty'];
                        $detail->harga = $item['harga'];
                        $detail->total = $item['harga'] * $item['qty'];
                        $detail->save();
                    }

                    DB::commit();

                    // Hapus cache setelah berhasil simpan
                    Cache::forget('order_data_' . $order_id);
                    Log::info('Order Created from Midtrans Callback: ' . $order_id);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error saving order from callback: ' . $e->getMessage());
                }
            } else {
                Log::warning('Midtrans Callback: No cached data found for ' . $order_id);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Step 3: Pengalihan setelah pembayaran (Finish)
     */
    public function sukses($token)
    {
        // Tunggu sebentar agar webhook (callback) selesai memproses data
        sleep(2);
        return redirect()->route('pelanggan.histori', ['token' => $token]);
    }

    /**
     * Menampilkan histori pesanan lunas untuk pelanggan
     */
    public function histori($token)
    {
        $meja = Meja::where('token', $token)->firstOrFail();
        $currentSessionId = session()->getId();

        $orders = Pesanan::with('detailPesanan.menu')
                    ->where('meja_id', $meja->id_meja)
                    ->where('session_id', $currentSessionId)
                    ->where('status_pembayaran', 'Lunas')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('pelanggan.histori', compact('meja', 'orders'));
    }

    /**
     * Detail Pesanan (Admin)
     */
    public function showDetail($id)
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu'])->findOrFail($id);
        return view('admin.detail-pesanan', compact('pesanan'));
    }
}
