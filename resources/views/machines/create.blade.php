@extends('layouts.app')

@section('title', 'Crear máquina')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Crear máquina</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Registra una máquina, descríbela y elige quiénes pueden usarla.
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

    <form method="POST" action="{{ route('machines.store') }}">
        @csrf

        {{-- ── Datos de la máquina ────────────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Datos de la máquina</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    El nombre debe ser único. La máquina se creará habilitada.
                </p>
            </div>

            <div class="max-w-3xl space-y-5 px-6 py-6">
                <div>
                    <label for="name" class="label">Nombre</label>

                    <input id="name" type="text" name="name" class="input" value="{{ old('name') }}"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>

                    @error('name')
                        <p id="name-error" class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="label">Descripción</label>

                    <textarea id="description" name="description" rows="3" class="input resize-y"
                        @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>

                    @error('description')
                        <p id="description-error" class="field-error">{{ $message }}</p>
                    @enderror

                    <p class="field-help">
                        Opcional. Modelo, ubicación o cualquier dato útil (máx. 1000 caracteres).
                    </p>
                </div>
            </div>
        </section>

        {{-- ── Usuarios que pueden usarla ─────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Usuarios habilitados</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Marca los usuarios que podrán usar esta máquina.
                    Puedes dejarla sin nadie y agregarlos después.
                </p>
            </div>

            <div class="px-6 py-6">
                @if ($users->isEmpty())
                    <p class="text-sm text-zinc-500">
                        No hay cuentas activas disponibles.
                    </p>
                @else
                    @php
                        $assignedUserIds = collect(old('user_ids', []))->map(fn ($id) => (int) $id);
                    @endphp

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($users as $user)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm transition-colors hover:bg-zinc-50">
                                <input type="checkbox"
                                       name="user_ids[]"
                                       value="{{ $user->id }}"
                                       class="mt-0.5 h-4 w-4 accent-zinc-900"
                                       @checked($assignedUserIds->contains($user->id))>

                                <span>
                                    <span class="block font-medium">
                                        {{ $user->name }} {{ $user->last_name }}
                                    </span>
                                    <span class="block text-xs text-zinc-500">
                                        {{ $user->email }} · {{ $user->roles->first()?->name ?? 'Sin rol' }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @error('user_ids')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <button type="submit" class="btn-primary">
                Crear máquina
            </button>

            @can('maquinas.ver')
                <a href="{{ route('machines.index') }}" class="btn-secondary min-h-11">
                    Volver al listado
                </a>
            @endcan
        </div>
    </form>
@endsection
