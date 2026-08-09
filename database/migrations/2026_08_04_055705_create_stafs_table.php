<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staf', function (Blueprint $table) {
            // 1. id_staf: int, Primary Key, Auto Increment
            $table->increments('id_staf');
            
            // 2. username: varchar(15), Not Null
            $table->string('username', 15);
            
            // 3. password: varchar(50), Not Null
            $table->string('password', 50);
            
            // 4. nama_staf: varchar(50), Not Null
            $table->string('nama_staf', 50);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stafs');
    }
};
