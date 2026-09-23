<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

Route::middleware(['auth', EnsureAccountIsActive::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/usuarios', [UserController::class, 'index'])
        ->middleware('can:usuarios.ver')
        ->name('users.index');

    Route::get('/usuarios/create', [UserController::class, 'create'])
        ->middleware('can:usuarios.crear')
        ->name('users.create');

    Route::post('/usuarios', [UserController::class, 'store'])
        ->middleware('can:usuarios.crear')
        ->name('users.store');

    Route::get('/usuarios/{usuario}/edit', [UserController::class, 'edit'])
        ->middleware('can:usuarios.editar')
        ->name('users.edit');

    Route::match(['put', 'patch'], '/usuarios/{usuario}', [UserController::class, 'update'])
        ->middleware('can:usuarios.editar')
        ->name('users.update');

    Route::patch('/usuarios/{user}/estado', [UserController::class, 'updateStatus'])
        ->middleware('can:usuarios.activar')
        ->name('users.status');

    Route::get('/maquinas', [MachineController::class, 'index'])
        ->middleware('can:maquinas.ver')
        ->name('machines.index');

    Route::get('/maquinas/create', [MachineController::class, 'create'])
        ->middleware('can:maquinas.crear')
        ->name('machines.create');

    Route::post('/maquinas', [MachineController::class, 'store'])
        ->middleware('can:maquinas.crear')
        ->name('machines.store');

    Route::get('/maquinas/{machine}/edit', [MachineController::class, 'edit'])
        ->middleware('can:maquinas.editar')
        ->name('machines.edit');

    Route::match(['put', 'patch'], '/maquinas/{machine}', [MachineController::class, 'update'])
        ->middleware('can:maquinas.editar')
        ->name('machines.update');

    Route::patch('/maquinas/{machine}/estado', [MachineController::class, 'updateStatus'])
        ->middleware('can:maquinas.activar')
        ->name('machines.status');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
