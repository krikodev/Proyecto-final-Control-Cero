@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">
        Hola, {{ auth()->user()->name }}
    </h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Bienvenido a tu espacio de trabajo en Control ATS.
    </p>

    @if (count($stats))
        <section class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Resumen del sistema">
            @foreach ($stats as $stat)
                <article class="panel p-6">
                    <p class="mb-4 text-sm text-zinc-500">{{ $stat['label'] }}</p>

                    <p class="text-3xl font-semibold tracking-tight">
                        {{ number_format($stat['value'], 0, ',', '.') }}
                    </p>
                </article>
            @endforeach
        </section>
    @endif

    @can('usuarios.ver')
        <section class="panel mt-7" aria-labelledby="recent-users-title">
            <div class="panel-header">
                <h2 id="recent-users-title" class="text-base font-semibold">
                    Usuarios recientes
                </h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Las últimas cinco cuentas registradas en el sistema.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left" aria-labelledby="recent-users-title">
                    <thead>
                        <tr>
                            <th scope="col" class="table-th">Usuario</th>
                            <th scope="col" class="table-th">Rol</th>
                            <th scope="col" class="table-th">Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentUsers as $user)
                            <tr>
                                <td class="table-td">
                                    {{ $user->name }} {{ $user->last_name }}
                                </td>

                                <td class="table-td">
                                    {{ $user->roles->first()?->name ?? 'Sin asignar' }}
                                </td>

                                <td class="table-td">
                                    <span class="badge {{ $user->is_active ? 'badge-active' : '' }}">
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="table-td text-zinc-500">
                                    Todavía no hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endcan

    @if (! count($stats))
        <section class="panel mt-7 p-6">
            <div class="py-2 text-center">
                <h2 class="text-base font-semibold">Tu cuenta está habilitada</h2>

                <p class="mt-2 text-sm leading-relaxed text-zinc-500">
                    Aún no hay información disponible para mostrar en tu inicio.
                </p>
            </div>
        </section>
    @endif
@endsection
