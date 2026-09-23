@extends('layouts.guest')

@section('title', 'Iniciar sesión · Control ATS')

@section('content')
    <div class="mb-7 text-center">
        <h1 class="text-[23px] font-semibold tracking-tight">Inicia sesión</h1>
        <p class="mt-2 text-sm text-zinc-500">Ingresa tus credenciales para continuar.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="mb-5">
            <label for="email" class="label">Correo electrónico</label>

            <input
                id="email"
                name="email"
                type="email"
                class="input"
                value="{{ old('email') }}"
                placeholder="nombre@empresa.com"
                autocomplete="username"
                maxlength="255"
                required
                autofocus
                @error('email')
                    aria-invalid="true"
                    aria-describedby="email-error"
                @enderror
            >

            @error('email')
                <p id="email-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="mb-5">
            <label for="password" class="label">Contraseña</label>

            <input
                id="password"
                name="password"
                type="password"
                class="input"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required
                @error('password')
                    aria-invalid="true"
                    aria-describedby="password-error"
                @enderror
            >

            @error('password')
                <p id="password-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full">
            Iniciar sesión
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-zinc-500">
        ¿Necesitas acceso? Contacta al administrador.
    </p>
@endsection
