@extends('layouts.guest')

@section('title', 'Iniciar sesión · Control ATS')

@section('content')
    <div class="heading">
        <h1>Inicia sesión</h1>
        <p>Ingresa tus credenciales para continuar.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Correo electrónico</label>

            <input
                id="email"
                name="email"
                type="email"
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
                <p id="email-error" class="error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Contraseña</label>

            <input
                id="password"
                name="password"
                type="password"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required
                @error('password')
                    aria-invalid="true"
                    aria-describedby="password-error"
                @enderror
            >

            @error('password')
                <p id="password-error" class="error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="button">
            Iniciar sesión
        </button>
    </form>

    <p class="footer">
        ¿Necesitas acceso? Contacta al administrador.
    </p>
@endsection