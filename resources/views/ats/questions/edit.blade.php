@extends('layouts.app')

@section('title', 'Editar pregunta')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Editar pregunta</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Los cambios se aplicarán a los nuevos registros; las respuestas ya
        guardadas se conservan tal como se registraron.
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

    <form method="POST" action="{{ route('ats.questions.update', $question) }}">
        @csrf
        @method('PUT')

        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Datos de la pregunta</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Responder esta pregunta: {{ $question->answers()->count() }} vez(es).
                </p>
            </div>

            @include('ats.questions._form', ['question' => $question])
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('ats.questions.index') }}" class="btn-secondary min-h-11">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Guardar cambios
            </button>
        </div>
    </form>
@endsection
