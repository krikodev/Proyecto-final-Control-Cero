<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Controllers\DashboardController;
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
    });

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');