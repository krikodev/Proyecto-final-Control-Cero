@extends('layouts.app')

@section('title', 'Crear pregunta')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Crear pregunta</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        La nueva pregunta se mostrará a los operadores con las opciones Sí / No.
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

    <form method="POST" action="{{ route('ats.questions.store') }}">
        @csrf

        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Datos de la pregunta</h2>
            </div>

            @include('ats.questions._form')
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('ats.questions.index') }}" class="btn-secondary min-h-11">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Crear pregunta
            </button>
        </div>
    </form>
@endsection
