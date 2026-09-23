@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Crear usuario</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Registra una cuenta de supervisor u operador.
    </p>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice-error" role="alert">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel mt-6">
        <div class="panel-header">
            <h2 class="text-base font-semibold">Datos de la cuenta</h2>

            <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                Todos los campos son obligatorios.
                La cuenta se creará activa.
            </p>
        </div>

        <form method="POST"
              action="{{ route('users.store') }}"
              class="max-w-3xl px-6 py-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach([
                    'name' => ['Nombres', 'text', 255],
                    'last_name' => ['Apellidos', 'text', 100],
                    'dni' => ['DNI', 'text', 8],
                    'email' => ['Correo electrónico', 'email', 255],
                ] as $field => [$label, $type, $length])
                    <div>
                        <label for="{{ $field }}" class="label">{{ $label }}</label>

                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="{{ $type }}"
                            class="input"
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

                <div class="sm:col-span-2">
                    <label for="role_id" class="label">Rol</label>

                    <select
                        id="role_id"
                        name="role_id"
                        class="input"
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
                        Los tres roles entran al panel web: el operador únicamente ve su registro EPP/ATS.
                    </p>
                </div>

                @foreach([
                    'password' => 'Contraseña',
                    'password_confirmation' => 'Confirmar contraseña',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="label">{{ $label }}</label>

                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="password"
                            class="input"
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

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <button type="submit" class="btn-primary">
                    Guardar usuario
                </button>

                @can('usuarios.ver')
                    <a href="{{ route('users.index') }}" class="btn-secondary min-h-11">
                        Volver al listado
                    </a>
                @endcan
            </div>
        </form>
    </section>
@endsection
