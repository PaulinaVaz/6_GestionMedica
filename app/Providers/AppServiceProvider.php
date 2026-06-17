<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Importante agregar esta línea

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forzar HTTPS si estamos en Heroku (entorno de producción)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
