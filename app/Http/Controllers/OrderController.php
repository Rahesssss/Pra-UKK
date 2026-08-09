<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Detail_Pesanan;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 1. Fungsi untuk memproses pesanan dan membuat link QRIS Midtrans
    public function store(Request $request)
    {
        // Tangkap data dari JavaScript di frontend
        $token_meja = $request->token_meja;
        $cart = $request->pesanan; 

        // Cari data meja berdasarkan token yang ada di URL
        $meja = Meja::where('token', $token_meja)->firstOrFail();

        // Hitung total harga dari seluruh isi keranjang
        $total_harga = 0;
        foreach ($cart as $item) {
            $total_harga += ($item['harga'] * $item['qty']);
        }

        // Gunakan Database Transaction agar aman (jika gagal, data batal masuk)
        DB::beginTransaction();
        try {
            // Simpan ke tabel 'pesanan'
            $order = new Pesanan();
            $order->meja_id = $meja->id_meja;
            $order->session_id = session()->getId(); // Simpan ID sesi device pelanggan
            $order->total_harga = $total_harga;
            $order->status_pesanan = 'pending';
            $order->save();

            // Simpan rincian item ke tabel 'detail_pesanan'
            foreach ($cart as $item) {
                $detail = new Detail_Pesanan();
                $detail->pesanan_id = $order->id;
                $detail->menu_id = $item['id'];
                $detail->jumlah = $item['qty'];
                $detail->harga = $item['harga'];
                $detail->total = $item['harga'] * $item['qty'];
                $detail->save();
            }

            // Konfigurasi Midtrans otomatis dari file .env
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Susun parameter transaksi untuk Midtrans (Dynamic QRIS)
            $params = [
                'transaction_details' => [
                    'order_id' => 'ORD-' . $order->id . '-' . time(), 
                    'gross_amount' => $total_harga,
                ],
                'customer_details' => [
                    'first_name' => 'Meja: ' . $meja->nama_meja,
                ],
                'callbacks' => [
                    'finish' => route('pesanan.sukses', ['token' => $token_meja]),
                ]
            ];

            // Minta Link Pembayaran (Redirect URL) ke server Midtrans
            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            // Jika sukses, simpan permanen ke database
            DB::commit();

            // Kembalikan URL pembayaran ke JavaScript frontend agar halaman terarah ke QRIS
            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentUrl
            ]);

        } catch (\Exception $e) {
            // Batalkan transaksi database jika ada error/kegagalan koneksi
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Fungsi Webhook / Callback untuk merespon otomatis saat pelanggan sukses bayar QRIS
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        // Validasi keamanan signature dari Midtrans
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                
                // Ekstrak ID asli pesanan kita
                $order_id_raw = explode('-', $request->order_id);
                $real_order_id = $order_id_raw[1];

                $order = Pesanan::find($real_order_id);
                
                if ($order) {
                    // Ubah status pembayaran menjadi Lunas dan pesanan menjadi diproses
                    $order->status_pembayaran = 'Lunas';
                    $order->status_pesanan = 'diproses';
                    $order->save();
                }
            }
        }
        
        return response()->json(['status' => 'success']);
    }

    // 3. Fungsi untuk menampilkan halaman sukses pembayaran, lalu redirect ke histori
    public function sukses($token)
    {
        $meja = Meja::where('token', $token)->firstOrFail();
        
        // Redirect langsung ke halaman histori
        return redirect()->route('pelanggan.histori', ['token' => $token]);
    }

    // 4. Fungsi untuk menampilkan halaman histori pesanan di sisi pelanggan berdasarkan token meja
    public function histori($token)
    {
        $meja = Meja::where('token', $token)->firstOrFail();
        $currentSessionId = session()->getId();

        $orders = Pesanan::with('detailPesanan.menu')
                    ->where('meja_id', $meja->id_meja)
                    ->where('session_id', $currentSessionId) // Hanya ambil pesanan dari device ini
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('pelanggan.histori', compact('meja', 'orders'));
    }
}