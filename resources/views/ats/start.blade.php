@extends('layouts.app')

@section('title', 'Iniciar turno')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight lg:text-[28px]">Iniciar turno</h1>

    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
        Máquina <span class="font-semibold text-zinc-700">{{ $machine->name }}</span>.
        Completa los tres pasos para abrir tu ATS de inicio.
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

    <form method="POST" action="{{ route('ats.store', $machine) }}" id="ats-start-form" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="photo_data" id="photo_data" value="{{ old('photo_data') }}">
        <input type="hidden" name="signature_data" id="signature_data" value="{{ old('signature_data') }}">

        {{-- ── 1. Foto en tiempo real ───────────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">1. Foto en tiempo real</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Activa la cámara y toma la foto. Si tu equipo no tiene cámara,
                    puedes subir una imagen desde el archivo.
                </p>
            </div>

            <div class="px-6 py-6">
                <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-950">
                    <video id="ats-video" playsinline autoplay
                           class="aspect-video w-full object-cover"></video>

                    <canvas id="ats-photo-canvas" class="hidden"></canvas>

                    <img id="ats-photo-preview" alt="Foto capturada"
                         class="aspect-video w-full object-cover {{ old('photo_data') ? '' : 'hidden' }}"
                         @if (old('photo_data')) src="{{ old('photo_data') }}" @endif>

                    <p id="ats-camera-placeholder"
                       class="absolute inset-0 grid place-items-center px-6 text-center text-sm text-zinc-400">
                        La cámara está apagada.
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" id="ats-camera-start" class="btn-secondary">Activar cámara</button>
                    <button type="button" id="ats-camera-capture" class="btn-secondary hidden">Tomar foto</button>
                    <button type="button" id="ats-camera-retake" class="btn-secondary hidden">Repetir foto</button>
                </div>

                <p id="ats-camera-note" class="field-help"></p>

                <div class="mt-5 border-t border-zinc-100 pt-5">
                    <label for="photo_file" class="label">o sube una foto</label>

                    <input id="photo_file" type="file" name="photo_file" accept="image/jpeg,image/png"
                           capture="environment"
                           class="block w-full cursor-pointer rounded-lg border border-dashed border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-zinc-700">

                    @error('photo_file')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    @error('photo_data')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ── 2. Firma ────────────────────────────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold">2. Tu firma</h2>

                    <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                        Dibújala en pantalla o sube un archivo con tu firma.
                    </p>
                </div>

                <div class="flex gap-2" role="tablist" aria-label="Método de firma">
                    <button type="button" role="tab" data-ats-tab="draw"
                            aria-selected="true"
                            class="btn-secondary">Dibujar</button>

                    <button type="button" role="tab" data-ats-tab="upload"
                            aria-selected="false"
                            class="btn-secondary">Subir archivo</button>
                </div>
            </div>

            <div class="px-6 py-6">
                <div id="ats-tab-draw">
                    <canvas id="ats-signature-canvas"
                            class="w-full touch-none rounded-lg border border-zinc-200 bg-white"
                            style="height: 200px;"></canvas>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="field-help !mt-0">
                            Usa el dedo o el mouse para firmar.
                        </p>

                        <button type="button" id="ats-signature-clear" class="btn-secondary">Limpiar</button>
                    </div>
                </div>

                <div id="ats-tab-upload" class="hidden">
                    <label for="signature_file" class="label">Archivo con tu firma</label>

                    <input id="signature_file" type="file" name="signature_file" accept="image/jpeg,image/png"
                           class="block w-full cursor-pointer rounded-lg border border-dashed border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-zinc-700">

                    <p class="field-help">
                        JPG o PNG, máximo 2 MB.
                    </p>

                    @error('signature_file')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                @error('signature_data')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- ── 3. Preguntas del ATS de inicio ──────────────────── --}}
        <section class="panel mt-6">
            <div class="panel-header">
                <h2 class="text-base font-semibold">3. ATS de inicio</h2>

                <p class="mt-1.5 text-[13px] leading-relaxed text-zinc-500">
                    Responde todas las preguntas para abrir tu registro.
                </p>
            </div>

            @include('ats.partials.questions', ['questions' => $questions])
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('ats.mine') }}" class="btn-secondary min-h-11">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Firmar y guardar ATS de inicio
            </button>
        </div>
    </form>
@endsection
