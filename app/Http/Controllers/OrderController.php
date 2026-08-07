<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Tangkap data yang dikirim oleh Javascript (fetch) di frontend
        $data = $request->json()->all();
        $token_meja = $data['token_meja'];
        $items = $data['items'];
        $total_harga = $data['total_harga'];

        // 2. Buat ID Pesanan Unik (Gabungan kata ORD dan waktu saat ini)
        $order_id = 'ORD-' . time();

        /* * Catatan untuk nanti: 
         * Di sini nanti kamu bisa menambahkan kode untuk menyimpan $items
         * ke dalam database MySQL kamu. Tapi untuk sekarang, kita fokus 
         * memunculkan QRIS nya dulu ya!
         */

        // 3. Konfigurasi Midtrans (Otomatis mengambil dari file .env)
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 4. Susun Data Transaksi untuk Dikirim ke Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order_id, 
                'gross_amount' => $total_harga, // ⬅️ Ini yang bikin nominal QRIS otomatis sesuai keranjang!
            ],
            'customer_details' => [
                'first_name' => 'Pelanggan Meja',
                'last_name' => $token_meja,
            ],
        ];

        try {
            // 5. Minta Snap Token ke API Midtrans
            $snapToken = Snap::getSnapToken($params);

            // 6. Kembalikan token ini ke Javascript agar popup muncul
            return response()->json([
                'status' => 'success',
                'snapToken' => $snapToken
            ]);
            
        } catch (\Exception $e) {
            // Jika ada error (misal API Key di .env salah atau internet mati)
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}