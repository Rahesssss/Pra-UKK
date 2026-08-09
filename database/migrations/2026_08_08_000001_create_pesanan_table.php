<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meja_id');
            $table->integer('total_harga');
            $table->string('status_pembayaran', 50)->default('Belum Bayar');
            $table->string('status_pesanan', 50)->default('Menunggu Pembayaran');
            $table->timestamps();
            
            $table->foreign('meja_id')->references('id_meja')->on('meja')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
