@extends('layouts.app')

@section('title', 'Editar máquina')

@section('content')
    @php
        $canToggleStatus = auth()->user()->can('maquinas.activar');
    @endphp

    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Editar máquina</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Actualiza los datos, los usuarios habilitados y el estado de
        {{ $machine->name }}.
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

    <form method="POST" action="{{ route('machines.update', $machine) }}">
        @csrf
        @method('PUT')

        {{-- ── Datos de la máquina ────────────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Datos de la máquina</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Estado actual:
                    <span class="badge {{ $machine->is_active ? 'badge-active' : '' }}">
                        {{ $machine->is_active ? 'Habilitada' : 'Inactiva' }}
                    </span>
                </p>
            </div>

            <div class="max-w-3xl space-y-5 px-6 py-6">
                <div>
                    <label for="name" class="label">Nombre</label>

                    <input id="name" type="text" name="name" class="input"
                           value="{{ old('name', $machine->name) }}"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>

                    @error('name')
                        <p id="name-error" class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="label">Descripción</label>

                    <textarea id="description" name="description" rows="3" class="input resize-y"
                        @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $machine->description) }}</textarea>

                    @error('description')
                        <p id="description-error" class="field-error">{{ $message }}</p>
                    @enderror

                    <p class="field-help">
                        Opcional. Modelos, ubicación o cualquier dato útil (máx. 1000 caracteres).
                    </p>
                </div>

                <div>
                    <span class="label">Habilitar máquina</span>

                    @if ($canToggleStatus)
                        <label class="flex max-w-xl cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-3">
                            {{-- Obligatorio: el checkbox solo envía valor cuando está marcado --}}
                            <input type="hidden" name="is_active" value="0">

                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   class="h-4 w-4 accent-zinc-900"
                                   @checked(old('is_active', $machine->is_active))>

                            <span class="text-sm font-medium">
                                La máquina está habilitada para operar
                            </span>
                        </label>

                        <p class="field-help">
                            Al desmarcarlo la máquina quedará inactiva.
                        </p>

                        @error('is_active')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    @else
                        <p class="text-sm">
                            {{ $machine->is_active ? 'Habilitada' : 'Inactiva' }}
                        </p>

                        <p class="field-help">
                            Se requiere el permiso para habilitar o desactivar máquinas.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Usuarios que pueden usarla ─────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">Usuarios habilitados</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Marca los usuarios que podrán usar esta máquina.
                    Desmarcar todo deja la máquina sin nadie habilitado.
                </p>
            </div>

            <div class="px-6 py-6">
                <input type="hidden" name="sync_users" value="1">

                @if ($users->isEmpty())
                    <p class="text-sm text-zinc-500">
                        No hay cuentas activas disponibles.
                    </p>
                @else
                    @php
                        $assignedUserIds = collect(
                            old('user_ids', $machine->users->pluck('id'))
                        )->map(fn ($id) => (int) $id);
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
                Guardar cambios
            </button>

            @can('maquinas.ver')
                <a href="{{ route('machines.index') }}" class="btn-secondary min-h-11">
                    Volver al listado
                </a>
            @endcan
        </div>
    </form>
@endsection
