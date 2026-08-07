<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('pesanan_id'); // Menghubungkan ke tabel pesanan
            $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
            
            $table->string('nama_menu');
            $table->decimal('harga', 12, 2);
            $table->integer('qty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
