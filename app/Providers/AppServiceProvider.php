<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Los permisos los registra spatie/laravel-permission en el Gate
     * (Gate::before → checkPermissionTo), por lo que no hace falta
     * definirlos a mano: @can, can() y el middleware "can:..." usan
     * directamente los permisos de la base de datos.
     */
    public function boot(): void
    {
        //
    }
}
