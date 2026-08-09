<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    // Karena nama tabel 'menu', Laravel otomatis mendeteksinya
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    protected $fillable = [
        'nama_menu',
        'kategori',
        'harga',
        'status_tersedia',
        'gambar',
    ];

    // Dihapus cast integer karena status_tersedia sekarang bertipe ENUM ('tersedia', 'habis')
    protected $casts = [
        'harga' => 'integer',
    ];
}
