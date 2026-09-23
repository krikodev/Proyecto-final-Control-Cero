@extends('layouts.app')

@section('title', 'Registro EPP/ATS')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Registro EPP/ATS</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Selecciona la máquina con la que vas a trabajar, registra tu foto y firma
        y abre tu ATS de inicio.
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

    {{-- ── Registro pendiente ────────────────────────────────── --}}
    @if ($pending)
        <section class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-5 sm:flex sm:items-center sm:justify-between sm:gap-6"
                 aria-labelledby="pending-title">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-amber-700">
                    Registro pendiente
                </p>

                <h2 id="pending-title" class="mt-1.5 text-base font-semibold text-amber-900">
                    {{ $pending->machine?->name }}
                </h2>

                <p class="mt-1 text-sm leading-relaxed text-amber-800">
                    Iniciado {{ $pending->started_at?->diffForHumans() ?? 'recientemente' }}.
                    Finalízalo cuando termines tu labor.
                </p>
            </div>

            <div class="mt-4 shrink-0 sm:mt-0">
                <a href="{{ route('ats.finish') }}" class="btn-primary">
                    Finalizar turno
                </a>
            </div>
        </section>
    @endif

    {{-- ── Máquinas disponibles ─────────────────────────────── --}}
    <section class="mt-6" aria-labelledby="machines-title">
        <div class="flex items-center justify-between gap-4">
            <h2 id="machines-title" class="text-base font-semibold">Máquinas disponibles</h2>

            <span class="badge">{{ $machines->count() }}</span>
        </div>

        @if ($machines->isEmpty())
            <div class="panel mt-3 px-6 py-8 text-center">
                <p class="text-sm text-zinc-500">
                    No tienes ninguna máquina habilitada. Contacta con tu supervisor.
                </p>
            </div>
        @else
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($machines as $machine)
                    <article class="panel flex flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-[15px] font-semibold leading-snug">
                                {{ $machine->name }}
                            </h3>

                            <span class="badge {{ $machine->is_active ? 'badge-active' : '' }}">
                                {{ $machine->is_active ? 'Habilitada' : 'Inactiva' }}
                            </span>
                        </div>

                        <p class="mt-2 flex-1 text-[13px] leading-relaxed text-zinc-500">
                            {{ $machine->description ? str($machine->description)->limit(90) : 'Sin descripción.' }}
                        </p>

                        <div class="mt-4">
                            @if (! $machine->is_active)
                                <span class="btn-secondary min-h-11 cursor-not-allowed opacity-60"
                                      aria-disabled="true">
                                    No disponible
                                </span>
                            @elseif ($pending)
                                <span class="btn-secondary min-h-11 cursor-not-allowed opacity-60"
                                      aria-disabled="true">
                                    Ya tienes un registro abierto
                                </span>
                            @else
                                <a href="{{ route('ats.start', $machine) }}" class="btn-primary">
                                    Iniciar turno
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ── Historial propio ─────────────────────────────────── --}}
    <section class="panel mt-7" aria-labelledby="history-title">
        <div class="panel-header">
            <h2 id="history-title" class="text-base font-semibold">Mis registros</h2>

            <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                ATS finalizados en tus últimas jornadas.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" aria-labelledby="history-title">
                <thead>
                    <tr>
                        <th scope="col" class="table-th">Máquina</th>
                        <th scope="col" class="table-th">Inicio</th>
                        <th scope="col" class="table-th">Fin</th>
                        <th scope="col" class="table-th">Estado</th>
                        <th scope="col" class="table-th">Detalle</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($history as $record)
                        <tr>
                            <td class="table-td font-medium">{{ $record->machine?->name ?? '—' }}</td>
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
                            <td colspan="5" class="table-td text-center text-zinc-500">
                                Todavía no has finalizado ningún registro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
