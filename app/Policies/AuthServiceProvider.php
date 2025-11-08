<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        // Agrega aquí otras policies si las tienes
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Aquí puedes definir Gates adicionales si es necesario
        
        // Ejemplo: Super Admin tiene acceso a todo
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super admin')) {
                return true;
            }
        });
    }
}