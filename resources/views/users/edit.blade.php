@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    @php
        $actor = auth()->user();
        $isSelf = $user->is($actor);
        $canManageRoles = $actor->can('roles.gestionar');
        $canToggleStatus = $actor->can('usuarios.activar');
        $currentRole = $user->roles->first();
        $directPermissionIds = $user->permissions->pluck('id');

        // Sin roles.gestionar solo pueden asignarse supervisor u operador;
        // si la cuenta actual es administrador no se ofrece el selector.
        $roleOptions = $canManageRoles
            ? $roles
            : $roles->whereIn('slug', ['supervisor', 'operador']);

        $showRoleSelect = ! $isSelf
            && ($currentRole === null || $roleOptions->contains('id', $currentRole->id));
    @endphp

    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Editar usuario</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Actualiza los datos, el rol, el estado y los permisos de
        {{ $user->name }} {{ $user->last_name }}.
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

    <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        {{-- ── Datos de la cuenta ─────────────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Datos de la cuenta</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Nombre, DNI y correo electrónico de la cuenta.
                </p>
            </div>

            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nombres</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name', $user->name) }}">
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="last_name" class="label">Apellidos</label>
                    <input id="last_name" type="text" name="last_name" class="input" value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="dni" class="label">DNI</label>
                    <input id="dni" type="text" name="dni" class="input" maxlength="8" inputmode="numeric"
                           value="{{ old('dni', $user->dni) }}">
                    @error('dni')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="email" class="label">Correo electrónico</label>
                    <input id="email" type="email" name="email" class="input" value="{{ old('email', $user->email) }}">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        {{-- ── Rol y estado (habilitar / inactivar) ───────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Rol y estado</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    El rol define los permisos base de la cuenta.
                    Estado actual:
                    <span class="badge {{ $user->is_active ? 'badge-active' : '' }}">
                        {{ $user->is_active ? 'Activa' : 'Inactiva' }}
                    </span>
                </p>
            </div>

            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                @if ($showRoleSelect)
                    <div>
                        <label for="role_id" class="label">Rol</label>

                        <select id="role_id" name="role_id" class="input"
                            @error('role_id') aria-invalid="true" aria-describedby="role-error" @enderror>
                            @foreach ($roleOptions as $role)
                                <option value="{{ $role->id }}"
                                    @selected(
                                        old('role_id')
                                            ? (int) old('role_id') === $role->id
                                            : $user->roles->contains('id', $role->id)
                                    )>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('role_id')
                            <p id="role-error" class="field-error">{{ $message }}</p>
                        @enderror

                        @unless ($canManageRoles)
                            <p class="field-help">
                                Para asignar el rol Administrador se requiere gestionar roles.
                            </p>
                        @endunless
                    </div>
                @else
                    <div>
                        <span class="label">Rol</span>
                        <p class="text-sm">
                            {{ $currentRole?->name ?? 'Sin asignar' }}
                        </p>
                        <p class="field-help">
                            {{ $isSelf
                                ? 'Tu propio rol no puede modificarse desde este formulario.'
                                : 'Este rol no puede modificarse con tus permisos actuales.' }}
                        </p>
                    </div>
                @endif

                <div>
                    @if ($canToggleStatus)
                        <span class="label">Habilitar cuenta</span>

                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-3">
                            {{-- Obligatorio: el checkbox solo envía valor cuando está marcado --}}
                            <input type="hidden" name="is_active" value="0">

                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   class="h-4 w-4 accent-zinc-900"
                                   @checked(old('is_active', $user->is_active))>

                            <span class="text-sm font-medium">
                                La cuenta está activa y puede iniciar sesión
                            </span>
                        </label>

                        <p class="field-help">
                            Al desmarcarlo la cuenta pierde el acceso al panel.
                            No puedes desactivar tu propia cuenta ni al último
                            administrador activo.
                        </p>

                        @error('is_active')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    @else
                        <span class="label">Habilitar cuenta</span>
                        <p class="text-sm">
                            {{ $user->is_active ? 'Activa' : 'Inactiva' }}
                        </p>
                        <p class="field-help">
                            Se requiere el permiso para activar o desactivar cuentas.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Checklist de permisos adicionales ──────────────── --}}
        @if ($canManageRoles)
            <section class="panel mt-6">
                <div class="panel-header">
                    <h2 class="text-base font-semibold">Permisos adicionales</h2>

                    <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                        Marca los permisos directos que se sumarán a los que
                        otorga el rol. Los permisos heredados del rol no se
                        pueden quitar desde aquí.
                    </p>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <input type="hidden" name="sync_permissions" value="1">

                    @foreach ($permissions->groupBy('module') as $module => $modulePermissions)
                        <fieldset>
                            <legend class="mb-3 text-[11px] font-semibold uppercase tracking-widest text-zinc-500">
                                {{ $module }}
                            </legend>

                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($modulePermissions as $permission)
                                    @if ($permissionsViaRoles->contains($permission->id))
                                        <label class="flex items-start gap-3 rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-2.5 text-sm text-zinc-500">
                                            <input type="checkbox"
                                                   class="mt-0.5 h-4 w-4 accent-zinc-400"
                                                   checked
                                                   disabled>
                                            <span>{{ $permission->title ?? $permission->name }}</span>
                                            <span class="badge ml-auto shrink-0">Rol</span>
                                        </label>
                                    @else
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm transition-colors hover:bg-zinc-50">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   class="mt-0.5 h-4 w-4 accent-zinc-900"
                                                   @checked($directPermissionIds->contains($permission->id))>
                                            <span>{{ $permission->title ?? $permission->name }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    @error('permissions')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <button type="submit" class="btn-primary">
                Guardar cambios
            </button>

            @can('usuarios.ver')
                <a href="{{ route('users.index') }}" class="btn-secondary min-h-11">
                    Volver al listado
                </a>
            @endcan
        </div>
    </form>
@endsection
