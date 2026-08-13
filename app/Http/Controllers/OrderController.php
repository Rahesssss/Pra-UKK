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
    private function getNomorUrutHarian(iterable $orders): array
    {
        $tanggalUnik = collect($orders)
            ->pluck('created_at')
            ->map(fn($date) => $date->toDateString())
            ->unique()
            ->values();

        $nomorUrut = [];

        foreach ($tanggalUnik as $tanggal) {
            $semuaPesananHariItu = Pesanan::whereDate('created_at', $tanggal)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id');

            foreach ($semuaPesananHariItu as $index => $pesananId) {
                $nomorUrut[$pesananId] = $index + 1;
            }
        }

        return $nomorUrut;
    }

    private function simpanPesananDariCache(array $cachedData): Pesanan
    {
        $order = new Pesanan();
        $order->meja_id = $cachedData['meja_id'];
        $order->total_harga = $cachedData['total_harga'];
        $order->session_id = $cachedData['session_id'];
        $order->status_pembayaran = 'Lunas';
        $order->status_pesanan = 'Menunggu';
        $order->catatan = $cachedData['catatan_umum'] ?? null;
        $order->save();

        foreach ($cachedData['cart'] as $item) {
            $detail = new Detail_Pesanan();
            $detail->pesanan_id = $order->id;
            $detail->menu_id = $item['id'];
            $detail->jumlah = $item['qty'];
            $detail->harga = $item['harga'];
            $detail->total = $item['harga'] * $item['qty'];
            $detail->catatan_item = $item['catatan_item'] ?? null;
            $detail->save();
        }

        return $order;
    }

    public function store(Request $request)
    {
        $token_meja = $request->token_meja;
        $cart = $request->pesanan;
        $catatanUmum = trim($request->input('catatan_umum', ''));

        $meja = Meja::where('token', $token_meja)->firstOrFail();

        $total_harga = 0;
        foreach ($cart as $item) {
            $total_harga += ($item['harga'] * $item['qty']);
        }

        try {
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $order_id = 'ORD-' . time() . '-' . rand(100, 999);

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
                    'finish' => route('pesanan.sukses', ['token' => $token_meja]) . '?order_id=' . $order_id,
                ],
            ];

            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            Cache::put('order_data_' . $order_id, [
                'meja_id' => $meja->id_meja,
                'cart' => $cart,
                'total_harga' => $total_harga,
                'session_id' => session()->getId(),
                'catatan_umum' => $catatanUmum,
            ], now()->addHours(2));

            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash(
            "sha512",
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        if ($hashed !== $request->signature_key) {
            Log::error('Midtrans Callback: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $order_id = $request->order_id;

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            $cachedData = Cache::get('order_data_' . $order_id);

            if ($cachedData) {
                DB::beginTransaction();
                try {
                    $this->simpanPesananDariCache($cachedData);
                    DB::commit();
                    Cache::forget('order_data_' . $order_id);
                    Log::info('Order Created from Callback: ' . $order_id);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error callback: ' . $e->getMessage());
                }
            } else {
                Log::warning('Callback: No cache for ' . $order_id);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function sukses(Request $request, $token)
    {
        $order_id = $request->query('order_id');

        if ($order_id) {
            $cachedData = Cache::get('order_data_' . $order_id);

            if ($cachedData) {
                DB::beginTransaction();
                try {
                    $this->simpanPesananDariCache($cachedData);
                    DB::commit();
                    Cache::forget('order_data_' . $order_id);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error sukses: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('pelanggan.histori', ['token' => $token]);
    }

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

        $nomorUrut = $this->getNomorUrutHarian($orders);

        return view('pelanggan.histori', compact('meja', 'orders', 'nomorUrut'));
    }

    public function showDetail($id)
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu'])->findOrFail($id);
        return view('admin.detail-pesanan', compact('pesanan'));
    }
}
