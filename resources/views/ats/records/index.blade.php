@extends('layouts.app')

@section('title', 'Registros EPP/ATS')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Registros EPP/ATS</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Consulta los registros de turno de todos los operadores.
    </p>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="panel mt-6" aria-labelledby="records-title">
        <div class="panel-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="records-title" class="text-sm font-medium text-zinc-500">
                    Resultados
                </h2>

                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ $records->total() }}
                </p>
            </div>

            <form method="GET" action="{{ route('ats.records') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label for="q" class="sr-only">Buscar registros</label>

                <input id="q" name="q" type="search" class="input sm:w-56"
                       placeholder="Operador o máquina..." value="{{ $search }}">

                <label for="machine" class="sr-only">Filtrar por máquina</label>

                <select id="machine" name="machine" class="input sm:w-52">
                    <option value="">Todas las máquinas</option>

                    @foreach ($machines as $machine)
                        <option value="{{ $machine->id }}" @selected((int) request('machine') === $machine->id)>
                            {{ $machine->name }}
                        </option>
                    @endforeach
                </select>

                <label for="status" class="sr-only">Filtrar por estado</label>

                <select id="status" name="status" class="input sm:w-44">
                    <option value="">Todos los estados</option>
                    <option value="pending" @selected($status === 'pending')>Pendiente</option>
                    <option value="completed" @selected($status === 'completed')>Finalizado</option>
                </select>

                <button type="submit" class="btn-secondary min-h-11">Filtrar</button>

                @if ($search || $status || request('machine'))
                    <a href="{{ route('ats.records') }}" class="btn-secondary min-h-11">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" aria-labelledby="records-title">
                <thead>
                    <tr>
                        <th scope="col" class="table-th">Operador</th>
                        <th scope="col" class="table-th">Máquina</th>
                        <th scope="col" class="table-th">Inicio</th>
                        <th scope="col" class="table-th">Fin</th>
                        <th scope="col" class="table-th">Estado</th>
                        <th scope="col" class="table-th">Detalle</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="table-td font-medium">
                                {{ $record->user?->name }} {{ $record->user?->last_name }}
                            </td>
                            <td class="table-td text-zinc-500">{{ $record->machine?->name ?? '—' }}</td>
                            <td class="table-td text-zinc-500">
                                {{ $record->started_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="table-td text-zinc-500">
                                {{ $record->finished_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="table-td">
                                <span class="badge {{ $record->isCompleted() ? 'badge-active' : '' }}">
                                    {{ $record->isCompleted() ? 'Finalizado' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="table-td">
                                <a href="{{ route('ats.show', $record) }}"
                                   class="font-medium text-zinc-700 underline underline-offset-4 hover:text-zinc-900">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td text-center text-zinc-500">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-4 py-4">
            {{ $records->links() }}
        </div>
    </section>
@endsection
