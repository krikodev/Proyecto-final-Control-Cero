<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
     */
    public function boot(): void
{
    $permissions = [
        'usuarios.ver',
        'usuarios.crear',
        'usuarios.editar',
        'usuarios.activar',

        'roles.gestionar',

        'equipos.ver',
        'equipos.crear',
        'equipos.editar',
        'equipos.activar',

        'habilitaciones.gestionar',

        'ats.ver_propios',
        'ats.ver_todos',
        'ats.crear',
        'ats.cerrar_propios',
        'ats.revisar',

        'reportes.ver',
    ];

    foreach ($permissions as $permission) {
        Gate::define(
            $permission,
            fn (User $user): bool => $user->hasPermission($permission)
        );
    }
}
}