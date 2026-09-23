@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Usuarios</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Consulta las cuentas registradas, sus roles y su estado.
    </p>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="panel mt-6" aria-labelledby="users-title">
        <div class="panel-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="users-title" class="text-sm font-medium text-zinc-500">
                    {{ $search ? 'Resultados de la búsqueda' : 'Total de usuarios del sistema' }}
                </h2>

                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ $users->total() }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('users.index') }}" class="flex gap-2">
                    <label for="q" class="sr-only">Buscar usuarios</label>

                    <input
                        id="q"
                        name="q"
                        type="search"
                        class="input sm:w-64"
                        placeholder="Nombre, DNI o correo..."
                        value="{{ $search }}"
                    >

                    <button type="submit" class="btn-secondary min-h-11">
                        Buscar
                    </button>

                    @if ($search)
                        <a href="{{ route('users.index') }}" class="btn-secondary min-h-11">
                            Limpiar
                        </a>
                    @endif
                </form>

                @can('usuarios.crear')
                    <a href="{{ route('users.create') }}" class="btn-primary">
                        Crear usuario
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" aria-labelledby="users-title">
                <thead>
                    <tr>
                        <th scope="col" class="table-th">Usuario</th>
                        <th scope="col" class="table-th">DNI</th>
                        <th scope="col" class="table-th">Correo electrónico</th>
                        <th scope="col" class="table-th">Rol</th>
                        <th scope="col" class="table-th">Estado</th>
                        @canany(['usuarios.editar', 'usuarios.activar'])
                            <th scope="col" class="table-th">Acciones</th>
                        @endcanany
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="table-td">
                                {{ $user->name }} {{ $user->last_name }}
                                @if ($user->id === auth()->id())
                                    <span class="badge ml-1">Tú</span>
                                @endif
                            </td>
                            <td class="table-td">{{ $user->dni ?? 'Sin registrar' }}</td>
                            <td class="table-td">{{ $user->email }}</td>
                            <td class="table-td">{{ $user->roles->first()?->name ?? 'Sin asignar' }}</td>
                            <td class="table-td">
                                <span class="badge {{ $user->is_active ? 'badge-active' : '' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            @canany(['usuarios.editar', 'usuarios.activar'])
                                <td class="table-td">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @can('usuarios.editar')
                                            <a href="{{ route('users.edit', $user->id) }}"
                                               class="btn-secondary"
                                               aria-label="Editar a {{ $user->name }} {{ $user->last_name }}">
                                                Editar
                                            </a>
                                        @endcan

                                        @can('usuarios.activar')
                                            @if ($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.status', $user) }}"
                                                      @if ($user->is_active) onsubmit="return confirm('¿Desactivar esta cuenta? Su acceso quedará bloqueado.');" @endif>
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="is_active"
                                                           value="{{ $user->is_active ? '0' : '1' }}">

                                                    <button type="submit"
                                                            class="btn-secondary {{ $user->is_active ? 'btn-danger' : '' }}"
                                                            aria-label="{{ $user->is_active ? 'Desactivar' : 'Activar' }} a {{ $user->name }} {{ $user->last_name }}">
                                                        {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAny(['usuarios.editar', 'usuarios.activar']) ? 6 : 5 }}"
                                class="table-td text-center text-zinc-500">
                                {{ $search ? 'No se encontraron usuarios que coincidan con la búsqueda.' : 'Todavía no hay usuarios registrados.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-4 py-4">
            {{ $users->links() }}
        </div>

        @error('is_active')
            <div class="notice-error mx-6 mb-6" role="alert">
                {{ $message }}
            </div>
        @enderror
    </section>
@endsection
