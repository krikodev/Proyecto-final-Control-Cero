@extends('layouts.app')

@section('title', 'Finalizar turno')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Finalizar turno</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Responde las preguntas de cierre. Tu firma del inicio se usa automáticamente.
    </p>

    @if ($errors->any())
        <div class="notice-error" role="alert">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Máquina</dt>
            <dd class="mt-1.5 text-sm font-semibold">{{ $record->machine?->name ?? '—' }}</dd>
        </div>

        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Inicio</dt>
            <dd class="mt-1.5 text-sm font-semibold">{{ $record->started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </div>

        <div class="panel px-5 py-4">
            <dt class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">Estado</dt>
            <dd class="mt-1.5">
                <span class="badge">Pendiente</span>
            </dd>
        </div>
    </dl>

    <form method="POST" action="{{ route('ats.finish.store', $record) }}">
        @csrf

        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Firma</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Autocompletada con la firma que registraste al iniciar tu turno.
                </p>
            </div>

            <div class="px-6 py-6">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($record->signature_path) }}"
                     alt="Firma de {{ $record->user?->name }}"
                     class="h-32 rounded-lg border border-zinc-200 bg-white object-contain p-3">
            </div>
        </section>

        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Preguntas de finalización</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Responde todas las preguntas para cerrar el ATS.
                </p>
            </div>

            @include('ats.partials.questions', ['questions' => $questions])
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('ats.mine') }}" class="btn-secondary min-h-11">
                Volver
            </a>

            <button type="submit" class="btn-primary">
                Finalizar ATS
            </button>
        </div>
    </form>
@endsection
