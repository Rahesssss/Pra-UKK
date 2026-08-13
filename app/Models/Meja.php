<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $primaryKey = 'id_meja';
    public $timestamps = false;
    
    protected $fillable = [
        'nama_meja',
        'status',
        'token'
    ];
}
