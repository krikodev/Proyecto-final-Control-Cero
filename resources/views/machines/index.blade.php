@extends('layouts.app')

@section('title', 'Máquinas')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Máquinas</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Consulta las máquinas registradas, quiénes pueden usarlas y su estado.
    </p>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="panel mt-6" aria-labelledby="machines-title">
        <div class="panel-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="machines-title" class="text-sm font-medium text-zinc-500">
                    {{ $search ? 'Resultados de la búsqueda' : 'Total de máquinas registradas' }}
                </h2>

                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ $machines->total() }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('machines.index') }}" class="flex gap-2">
                    <label for="q" class="sr-only">Buscar máquinas</label>

                    <input
                        id="q"
                        name="q"
                        type="search"
                        class="input sm:w-64"
                        placeholder="Nombre o descripción..."
                        value="{{ $search }}"
                    >

                    <button type="submit" class="btn-secondary min-h-11">
                        Buscar
                    </button>

                    @if ($search)
                        <a href="{{ route('machines.index') }}" class="btn-secondary min-h-11">
                            Limpiar
                        </a>
                    @endif
                </form>

                @can('maquinas.crear')
                    <a href="{{ route('machines.create') }}" class="btn-primary">
                        Crear máquina
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" aria-labelledby="machines-title">
                <thead>
                    <tr>
                        <th scope="col" class="table-th">Máquina</th>
                        <th scope="col" class="table-th">Descripción</th>
                        <th scope="col" class="table-th">Usuarios habilitados</th>
                        <th scope="col" class="table-th">Estado</th>
                        @canany(['maquinas.editar', 'maquinas.activar'])
                            <th scope="col" class="table-th">Acciones</th>
                        @endcanany
                    </tr>
                </thead>

                <tbody>
                    @forelse($machines as $machine)
                        <tr>
                            <td class="table-td font-medium">{{ $machine->name }}</td>

                            <td class="table-td text-zinc-500">
                                {{ $machine->description ? str($machine->description)->limit(70) : 'Sin descripción' }}
                            </td>

                            <td class="table-td">
                                <span class="badge">{{ $machine->users_count }}</span>

                                @if ($machine->users_count > 0)
                                    <span class="ml-1 text-zinc-500">
                                        {{ $machine->users->take(3)->map(fn ($user) => $user->name.' '.$user->last_name)->implode(', ') }}
                                        @if ($machine->users_count > 3)
                                            <span class="text-xs">+{{ $machine->users_count - 3 }} más</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="ml-1 text-zinc-500">Nadie habilitado</span>
                                @endif
                            </td>

                            <td class="table-td">
                                <span class="badge {{ $machine->is_active ? 'badge-active' : '' }}">
                                    {{ $machine->is_active ? 'Habilitada' : 'Inactiva' }}
                                </span>
                            </td>

                            @canany(['maquinas.editar', 'maquinas.activar'])
                                <td class="table-td">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @can('maquinas.editar')
                                            <a href="{{ route('machines.edit', $machine) }}"
                                               class="btn-secondary"
                                               aria-label="Editar {{ $machine->name }}">
                                                Editar
                                            </a>
                                        @endcan

                                        @can('maquinas.activar')
                                            <form method="POST" action="{{ route('machines.status', $machine) }}"
                                                  @if ($machine->is_active) onsubmit="return confirm('¿Desactivar esta máquina?');" @endif>
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="is_active"
                                                       value="{{ $machine->is_active ? '0' : '1' }}">

                                                <button type="submit"
                                                        class="btn-secondary {{ $machine->is_active ? 'btn-danger' : '' }}"
                                                        aria-label="{{ $machine->is_active ? 'Desactivar' : 'Habilitar' }} {{ $machine->name }}">
                                                    {{ $machine->is_active ? 'Desactivar' : 'Habilitar' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAny(['maquinas.editar', 'maquinas.activar']) ? 5 : 4 }}"
                                class="table-td text-center text-zinc-500">
                                {{ $search ? 'No se encontraron máquinas que coincidan con la búsqueda.' : 'Todavía no hay máquinas registradas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-4 py-4">
            {{ $machines->links() }}
        </div>

        @error('is_active')
            <div class="notice-error mx-6 mb-6" role="alert">
                {{ $message }}
            </div>
        @enderror
    </section>
@endsection
