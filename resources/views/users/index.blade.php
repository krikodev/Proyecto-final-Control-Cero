@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <h1>Usuarios</h1>

    <p class="intro muted">
        Consulta las cuentas registradas, sus roles y su estado.
    </p>

    <section class="panel" aria-labelledby="users-title">
        @can('usuarios.crear')
            <div class="form-actions">
                <a href="{{ route('users.create') }}" class="btn-primary">
                    Crear usuario
                </a>
            </div>
        @endcan
        <div class="panel-header">
            <h2 id="users-title">Cuentas del sistema</h2>

            <p class="panel-description muted">
                {{ $users->total() }}
                {{ $users->total() === 1 ? 'usuario registrado' : 'usuarios registrados' }}.
            </p>
        </div>

        <div class="table-scroll">
            <table aria-labelledby="users-title">
                <thead>
                    <tr>
                        <th scope="col">Usuario</th>
                        <th scope="col">DNI</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado</th>
                        @canany(['usuarios.editar', 'usuarios.activar'])
                            <th scope="col">Acciones</th>
                        @endcanany
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                {{ $user->name }} {{ $user->last_name }}

                                @if($user->id === auth()->id())
                                    <span class="badge">Tú</span>
                                @endif
                            </td>

                            <td>{{ $user->dni ?? 'Sin registrar' }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                {{ $user->role?->name ?? 'Sin asignar' }}
                            </td>

                            <td>
                                <span class="badge {{ $user->is_active ? 'active' : '' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            @canany(['usuarios.editar', 'usuarios.activar'])
                            <td>
                                <div class="row-actions">
                                    @can('usuarios.editar')
                                        <a href="{{ route('users.edit', $user) }}"
                                        class="pagination-link"
                                        aria-label="Editar a {{ $user->name }} {{ $user->last_name }}">
                                            Editar
                                        </a>
                                    @endcan

                                    @can('usuarios.activar')
                                        @if($user->id !== auth()->id())
                                            <form method="POST"
                                                action="{{ route('users.status', $user) }}"
                                                @if($user->is_active)
                                                    onsubmit="return confirm('¿Desactivar esta cuenta? Su acceso quedará bloqueado.');"
                                                @endif>
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden"
                                                    name="is_active"
                                                    value="{{ $user->is_active ? '0' : '1' }}">

                                                <button type="submit"
                                                        class="pagination-link {{ $user->is_active ? 'action-danger' : '' }}"
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
                                  class="muted">
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(session('success'))
            <div class="notice-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @error('is_active')
            <div class="notice-error" role="alert">
                {{ $message }}
            </div>
        @enderror
        
        @if($users->hasPages())
            <nav class="pagination" aria-label="Paginación de usuarios">
                @if($users->onFirstPage())
                    <span class="pagination-link disabled" aria-disabled="true">
                        Anterior
                    </span>
                @else
                    <a class="pagination-link"
                       href="{{ $users->previousPageUrl() }}"
                       rel="prev">
                        Anterior
                    </a>
                @endif

                <span class="muted">
                    Página {{ $users->currentPage() }}
                    de {{ $users->lastPage() }}
                </span>

                @if($users->hasMorePages())
                    <a class="pagination-link"
                       href="{{ $users->nextPageUrl() }}"
                       rel="next">
                        Siguiente
                    </a>
                @else
                    <span class="pagination-link disabled" aria-disabled="true">
                        Siguiente
                    </span>
                @endif
            </nav>
        @endif
    </section>
@endsection