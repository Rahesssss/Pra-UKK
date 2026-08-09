<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    protected $fillable = [
        'nama_menu',
        'kategori',
        'harga',
        'status_tersedia',
        'gambar',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];
}
