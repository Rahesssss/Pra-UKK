<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->string('id')->primary(); // Format ID: ORD-123456
            $table->string('token_meja')->nullable();
            $table->decimal('total_harga', 12, 2);
            $table->string('status_pembayaran')->default('Belum Bayar'); // Belum Bayar, Lunas
            $table->string('status_pesanan')->default('Menunggu Pembayaran'); // Menunggu Pembayaran, Diproses Dapur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};