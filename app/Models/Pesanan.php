<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan'; // Nama tabel

    protected $fillable = [
        'meja_id',
        'total_harga',
        'session_id',
        'status_pembayaran',
        'status_pesanan',
        'catatan',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'id_meja');
    }

    public function detailPesanan()
    {
        return $this->hasMany(Detail_Pesanan::class, 'pesanan_id');
    }
}
