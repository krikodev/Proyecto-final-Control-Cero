<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;    
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

Route::middleware(['auth', EnsureAccountIsActive::class])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/usuarios', [UserController::class, 'index'])
            ->middleware('can:usuarios.ver')
            ->name('users.index');

        Route::get('/usuarios/crear', [UserController::class, 'create'])
            ->middleware('can:usuarios.crear')
            ->name('users.create');

        Route::post('/usuarios', [UserController::class, 'store'])
            ->middleware('can:usuarios.crear')
            ->name('users.store');

        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])
            ->middleware('can:usuarios.editar')
            ->name('users.edit');

        Route::put('/usuarios/{user}', [UserController::class, 'update'])
            ->middleware('can:usuarios.editar')
            ->name('users.update');

        Route::patch('/usuarios/{user}/estado', [UserController::class, 'updateStatus'])
            ->middleware('can:usuarios.activar')
            ->name('users.status');
    });

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');