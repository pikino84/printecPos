<?php

namespace App\Providers;

use App\Services\CFDI\CFDIService;
use App\Services\CFDI\MockPACProvider;
use App\Services\CFDI\PACInterface;
use App\Services\CFDI\ProdigiaProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el proveedor PAC según configuración
        $this->app->singleton(PACInterface::class, function ($app) {
            $provider = config('cfdi.default_provider', 'mock');

            return match ($provider) {
                'prodigia' => new ProdigiaProvider(),
                default => new MockPACProvider(),
            };
        });

        // Registrar el servicio CFDI con el proveedor configurado
        $this->app->singleton(CFDIService::class, function ($app) {
            return new CFDIService($app->make(PACInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS solo en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
