<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            // 1. id_menu: int, Primary Key, Auto Increment
            $table->increments('id_menu'); 
            
            // 2. nama_menu: varchar(100), Not Null
            $table->string('nama_menu', 100);
            
            // 3. kategori: varchar(50), Not Null
            $table->string('kategori', 50);
            
            // 4. harga: int, Not Null
            $table->integer('harga');
            
            // 5. status_tersedia: tinyint(1), Not Null, Default 1
            $table->tinyInteger('status_tersedia')->default(1);
            
            // 6. gambar: varchar(255), Not Null
            $table->string('gambar', 255);
            
            // Catatan: Jika di database aslimu tidak ada kolom created_at 
            // dan updated_at, jangan gunakan $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
