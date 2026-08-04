<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stafs', function (Blueprint $table) {
            // 1. id_staf: int, Primary Key, Auto Increment
            $table->increments('id_staf');
            
            // 2. username: varchar(50), Not Null
            $table->string('username', 50);
            
            // 3. password: varchar(255), Not Null
            $table->string('password', 255);
            
            // 4. nama_staf: varchar(100), Not Null
            $table->string('nama_staf', 100);
            
            // 5 & 6. created_at & updated_at: timestamp dengan default CURRENT_TIMESTAMP
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stafs');
    }
};
