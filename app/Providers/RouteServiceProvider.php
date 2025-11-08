<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        parent::boot();
        
        // ✅ BINDING PERSONALIZADO: Carga relaciones antes de evaluar Policy
        Route::bind('own_product', function ($value) {
            return \App\Models\Product::with(['partner', 'productCategory'])
                ->where('id', $value)
                ->where('is_own_product', 1)
                ->firstOrFail();
        });
        
        $this->configureRateLimiting();
        
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            //return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
