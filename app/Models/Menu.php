<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    // Karena nama tabel 'menus', Laravel otomatis mendeteksinya
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    protected $fillable = [
        'nama_menu',
        'kategori',
        'harga',
        'status_tersedia',
    ];

    // Cast agar status_tersedia diperlakukan sebagai boolean/integer
    protected $casts = [
        'harga'           => 'decimal:2',
        'status_tersedia' => 'integer',
    ];
}
