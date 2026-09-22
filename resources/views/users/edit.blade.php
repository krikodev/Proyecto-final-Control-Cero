@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <h1>Editar usuario</h1>

    <p class="intro muted">
        Actualiza los datos de {{ $user->name }} {{ $user->last_name }}.
    </p>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice-error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel">
        <div class="panel-header">
            <h2>Datos de la cuenta</h2>

            <p class="panel-description muted">
                Rol: {{ $user->role?->name ?? 'Sin asignar' }} ·
                Estado: {{ $user->is_active ? 'Activo' : 'Inactivo' }}
            </p>
        </div>

        <form method="POST" action="{{ route('users.update', $user->id) }}" class="user-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-field">
                    <label for="name">Nombre: </label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror

                    <label for="last_name">Apellidos: </label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name')<span class="field-error">{{ $message }}</span>@enderror

                    <label for="dni">DNI: </label>
                    <input id="dni" type="text" name="dni" value="{{ old('dni', $user->dni) }}">
                    @error('dni')<span class="field-error">{{ $message }}</span>@enderror

                    <label for="email">Email: </label>
                    <input id="email" type="text" name="email" value="{{ old('email', $user->email) }}">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Guardar cambios
                </button>

                @can('usuarios.ver')
                    <a href="{{ route('users.index') }}" class="pagination-link">
                        Volver al listado
                    </a>
                @endcan
            </div>

        </form>
    </section>
@endsection
