<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staf extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staf'; // Nama tabel di database

    protected $fillable = [
        'username',
        'password',
        'nama_staf', // Menyesuaikan kolom di database kamu
    ];

    protected $hidden = [
        'password',
    ];
}
