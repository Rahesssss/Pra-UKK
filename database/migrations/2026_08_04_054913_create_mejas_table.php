<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meja', function (Blueprint $table) {
            $table->id('id_meja'); // Primary Key (Otomatis INT, Auto Increment)
            $table->string('nama_meja', 15); // Contoh: "Meja 1"
            $table->enum('status', ['Active', 'Nonaktif'])->default('Active'); // Hanya bisa diisi 2 nilai ini
            $table->string('token', 20)->unique(); // Kode acak QR (Otomatis Unique)
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('meja');
    }
};
