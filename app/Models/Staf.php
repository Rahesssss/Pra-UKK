<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Staf extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staf';

    protected $fillable = [
        'username',
        'password',
        'nama_staf',
    ];

    protected $hidden = [
        'password',
    ];
}
