@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <h1>Hola, {{ auth()->user()->name }}</h1>

    <p class="intro muted">
        Bienvenido a tu espacio de trabajo en Control ATS.
    </p>

    @if(count($stats))
        <section class="stats" aria-label="Resumen del sistema">
            @foreach($stats as $stat)
                <article class="stat">
                    <p class="stat-label">{{ $stat['label'] }}</p>

                    <p class="stat-value">
                        {{ number_format($stat['value'], 0, ',', '.') }}
                    </p>
                </article>
            @endforeach
        </section>
    @endif

    @can('usuarios.ver')
        <section class="panel" aria-labelledby="recent-users-title">
            <div class="panel-header">
                <h2 id="recent-users-title">Usuarios recientes</h2>

                <p class="panel-description muted">
                    Las últimas cinco cuentas registradas en el sistema.
                </p>
            </div>

            <div class="table-scroll">
                <table aria-labelledby="recent-users-title">
                    <thead>
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentUsers as $user)
                            <tr>
                                <td>
                                    {{ $user->name }} {{ $user->last_name }}
                                </td>

                                <td>
                                    {{ $user->role?->name ?? 'Sin asignar' }}
                                </td>

                                <td>
                                    <span class="badge {{ $user->is_active ? 'active' : '' }}">
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="muted">
                                    Todavía no hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endcan

    @if(! count($stats))
        <section class="panel">
            <div class="empty">
                <h2>Tu cuenta está habilitada</h2>

                <p class="panel-description muted">
                    Aún no hay información disponible para mostrar en tu inicio.
                </p>
            </div>
        </section>
    @endif
@endsection