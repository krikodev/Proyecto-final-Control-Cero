@extends('layouts.app')

@section('title', 'Preguntas ATS')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Preguntas ATS</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Administra las preguntas con checklist Sí / No: las de <strong class="font-semibold text-zinc-700">inicio</strong>
        se responden al abrir el turno y las de <strong class="font-semibold text-zinc-700">finalización</strong>
        al cerrarlo.
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

    <section class="panel mt-6" aria-labelledby="questions-title">
        <div class="panel-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="questions-title" class="text-sm font-medium text-zinc-500">Total de preguntas</h2>

                <p class="mt-1 text-2xl font-semibold tracking-tight">{{ $questions->count() }}</p>
            </div>

            <a href="{{ route('ats.questions.create') }}" class="btn-primary">
                Crear pregunta
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" aria-labelledby="questions-title">
                <thead>
                    <tr>
                        <th scope="col" class="table-th">#</th>
                        <th scope="col" class="table-th">Pregunta</th>
                        <th scope="col" class="table-th">Etapa</th>
                        <th scope="col" class="table-th">Orden</th>
                        <th scope="col" class="table-th">Estado</th>
                        <th scope="col" class="table-th">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($questions as $question)
                        <tr>
                            <td class="table-td text-zinc-500">{{ $question->id }}</td>

                            <td class="table-td font-medium">{{ $question->title }}</td>

                            <td class="table-td">
                                <span class="badge">
                                    {{ $question->isStartStage() ? 'Inicio' : 'Finalización' }}
                                </span>
                            </td>

                            <td class="table-td text-zinc-500">{{ $question->position }}</td>

                            <td class="table-td">
                                <span class="badge {{ $question->is_active ? 'badge-active' : '' }}">
                                    {{ $question->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>

                            <td class="table-td">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('ats.questions.edit', $question) }}"
                                       class="btn-secondary"
                                       aria-label="Editar pregunta {{ $question->id }}">
                                        Editar
                                    </a>

                                    <form method="POST" action="{{ route('ats.questions.status', $question) }}">
                                        @csrf
                                        @method('PATCH')

                                        <input type="hidden" name="is_active"
                                               value="{{ $question->is_active ? '0' : '1' }}">

                                        <button type="submit"
                                                class="btn-secondary {{ $question->is_active ? 'btn-danger' : '' }}">
                                            {{ $question->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('ats.questions.destroy', $question) }}"
                                          onsubmit="return confirm('¿Eliminar esta pregunta?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-secondary btn-danger"
                                                aria-label="Eliminar pregunta {{ $question->id }}">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td text-center text-zinc-500">
                                Todavía no hay preguntas. Crea la primera.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
