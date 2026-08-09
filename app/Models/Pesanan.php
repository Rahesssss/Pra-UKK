<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function detailPesanan()
    {
        return $this->hasMany(Detail_Pesanan::class, 'pesanan_id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'id_meja');
    }
}