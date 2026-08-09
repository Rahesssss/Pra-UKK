<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail_Pesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id_menu');
    }
}