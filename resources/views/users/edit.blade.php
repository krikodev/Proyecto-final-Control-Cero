@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <h1>Editar usuario</h1>

    <p class="intro muted">
        Actualiza los datos de {{ $user->name }} {{ $user->last_name }}.
    </p>

    @if(session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
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

        <form method="POST"
              action="{{ route('users.update', $user) }}"
              class="user-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                @foreach([
                    'name' => ['Nombres', 'text', 255],
                    'last_name' => ['Apellidos', 'text', 100],
                    'dni' => ['DNI', 'text', 8],
                    'email' => ['Correo electrónico', 'email', 255],
                ] as $field => [$label, $type, $length])
                    <div class="form-field">
                        <label for="{{ $field }}">{{ $label }}</label>

                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="{{ $type }}"
                            value="{{ old($field, $user->{$field}) }}"
                            maxlength="{{ $length }}"
                            required
                            @if($field === 'dni')
                                inputmode="numeric"
                                pattern="[0-9]{8}"
                            @endif
                            @error($field)
                                aria-invalid="true"
                                aria-describedby="{{ $field }}-error"
                            @enderror
                        >

                        @error($field)
                            <p id="{{ $field }}-error" class="field-error">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach

                <div class="form-wide">
                    <h2>Cambiar contraseña</h2>

                    <p class="field-help">
                        Deja ambos campos vacíos para conservar la actual.
                        Si ingresas una nueva, utiliza al menos 12 caracteres.
                    </p>
                </div>

                @foreach([
                    'password' => 'Nueva contraseña',
                    'password_confirmation' => 'Confirmar nueva contraseña',
                ] as $field => $label)
                    <div class="form-field">
                        <label for="{{ $field }}">{{ $label }}</label>

                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="password"
                            autocomplete="new-password"
                            minlength="12"
                            @error($field)
                                aria-invalid="true"
                                aria-describedby="{{ $field }}-error"
                            @enderror
                        >

                        @error($field)
                            <p id="{{ $field }}-error" class="field-error">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach
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