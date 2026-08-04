<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja'; // Nama tabel di database
    // Beritahu Laravel bahwa Primary Key kita bernama 'id_meja'
    protected $primaryKey = 'id_meja';
    
    // Izinkan kolom-kolom ini diisi data dari form
    protected $fillable = [
        'nama_meja',
        'status',
        'token'
    ];
}