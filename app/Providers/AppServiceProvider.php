<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 1. Tambahkan ini di bagian atas

class AppServiceProvider extends ServiceProvider
{
    // ...

    public function boot(): void
    {
        // 2. Tambahkan baris ini
        if (request()->server('HTTP_X_FORWARDED_PROTO') == 'https' || env('APP_ENV') == 'production') {
            URL::forceScheme('https');
        }
        
        // Atau jika hanya untuk testing ngrok, gunakan yang ini saja:
        // \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}