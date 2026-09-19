@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
    <h1>Crear usuario</h1>

    <p class="intro muted">
        Registra una cuenta de supervisor u operador.
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
                Todos los campos son obligatorios.
                La cuenta se creará activa.
            </p>
        </div>

        <form method="POST"
              action="{{ route('users.store') }}"
              class="user-form">
            @csrf

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
                            value="{{ old($field) }}"
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

                <div class="form-field form-wide">
                    <label for="role_id">Rol</label>

                    <select
                        id="role_id"
                        name="role_id"
                        required
                        @error('role_id')
                            aria-invalid="true"
                            aria-describedby="role-error"
                        @enderror
                    >
                        <option value="">Selecciona un rol</option>

                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                @selected(old('role_id') == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_id')
                        <p id="role-error" class="field-error">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="field-help">
                        Supervisor: panel web. Operador: aplicación móvil.
                    </p>
                </div>

                @foreach([
                    'password' => 'Contraseña',
                    'password_confirmation' => 'Confirmar contraseña',
                ] as $field => $label)
                    <div class="form-field">
                        <label for="{{ $field }}">{{ $label }}</label>

                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="password"
                            autocomplete="new-password"
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

            <p class="field-help">
                Utiliza una contraseña de al menos 12 caracteres.
            </p>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Guardar usuario
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