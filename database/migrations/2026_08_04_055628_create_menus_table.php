<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            // 1. id_menu: int, Primary Key, Auto Increment
            $table->increments('id_menu'); 
            
            // 2. nama_menu: varchar(100), Not Null
            $table->string('nama_menu', 100);
            
            // 3. kategori: varchar(50), Not Null
            $table->string('kategori', 50);
            
            // 4. harga: int, Not Null
            $table->integer('harga');
            
            // 5. status_tersedia: enum('tersedia', 'habis'), Not Null, Default 'tersedia' (Diubah dari tinyInteger)
            $table->enum('status_tersedia', ['tersedia', 'habis'])->default('tersedia');
            
            // 6. gambar: varchar(255), Not Null
            $table->string('gambar', 255);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
