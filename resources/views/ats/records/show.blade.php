@extends('layouts.app')

@section('title', 'Detalle del registro')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Detalle del registro</h1>

            <p class="mt-2 text-sm leading-relaxed text-zinc-500">
                {{ $record->user?->name }} {{ $record->user?->last_name }}
                · {{ $record->machine?->name ?? 'Sin máquina' }}
            </p>
        </div>

        <a href="{{ auth()->user()->can('ats.ver_todos') ? route('ats.records') : route('ats.mine') }}"
           class="btn-secondary min-h-11">
            Volver
        </a>
    </div>

    @if (session('success'))
        <div class="notice-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Estado</dt>
            <dd class="mt-1.5">
                <span class="badge {{ $record->isCompleted() ? 'badge-active' : '' }}">
                    {{ $record->isCompleted() ? 'Finalizado' : 'Pendiente' }}
                </span>
            </dd>
        </div>

        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Inicio</dt>
            <dd class="mt-1.5 text-sm font-semibold">
                {{ $record->started_at?->format('d/m/Y H:i') ?? '—' }}
            </dd>
        </div>

        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Fin</dt>
            <dd class="mt-1.5 text-sm font-semibold">
                {{ $record->finished_at?->format('d/m/Y H:i') ?? '—' }}
            </dd>
        </div>

        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Operador</dt>
            <dd class="mt-1.5 break-words text-sm font-semibold">
                {{ $record->user?->email ?? '—' }}
            </dd>
        </div>
    </dl>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="panel overflow-hidden">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Foto del operador</h2>
            </div>

            <div class="p-6">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($record->photo_path) }}"
                     alt="Foto del operador"
                     class="w-full rounded-lg border border-zinc-200 bg-zinc-50 object-cover">
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Firma</h2>
            </div>

            <div class="p-6">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($record->signature_path) }}"
                     alt="Firma del operador"
                     class="h-40 rounded-lg border border-zinc-200 bg-white object-contain p-4">
            </div>
        </section>
    </div>

    @foreach ([
        'ATS de inicio' => $startAnswers,
        'ATS de finalización' => $finishAnswers,
    ] as $title => $answers)
        <section class="panel mt-6" aria-label="{{ $title }}">
            <div class="panel-header">
                <h2 class="text-base font-semibold">{{ $title }}</h2>
            </div>

            @if ($answers->isEmpty())
                <p class="px-6 py-6 text-sm text-zinc-500">Sin respuestas registradas.</p>
            @else
                <ul>
                    @foreach ($answers as $answer)
                        <li class="flex flex-col gap-2 border-t border-zinc-100 px-6 py-4 first:border-t-0 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                            <span class="text-sm leading-relaxed">
                                {{ $answer->question?->title ?? 'Pregunta eliminada' }}
                            </span>

                            <span class="badge {{ $answer->answer ? 'badge-active' : '' }} shrink-0">
                                {{ $answer->answer ? 'Sí' : 'No' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
@endsection
