<div class="max-w-3xl space-y-5 px-6 py-6">
    <div>
        <label for="title" class="label">Pregunta</label>

        <input id="title" type="text" name="title" class="input" maxlength="255"
               placeholder="¿El área de trabajo está despejada?"
               value="{{ old('title', $question->title ?? '') }}"
               @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>

        @error('title')
            <p id="title-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="stage" class="label">Etapa</label>

            <select id="stage" name="stage" class="input"
                    @error('stage') aria-invalid="true" aria-describedby="stage-error" @enderror>
                <option value="start" @selected(old('stage', $question->stage ?? '') === 'start')>
                    ATS de inicio
                </option>
                <option value="finish" @selected(old('stage', $question->stage ?? '') === 'finish')>
                    ATS de finalización
                </option>
            </select>

            @error('stage')
                <p id="stage-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="position" class="label">Orden</label>

            <input id="position" type="number" name="position" class="input" min="0" max="999"
                   value="{{ old('position', $question->position ?? 0) }}"
                   @error('position') aria-invalid="true" aria-describedby="position-error" @enderror>

            @error('position')
                <p id="position-error" class="field-error">{{ $message }}</p>
            @enderror

            <p class="field-help">Menor número = se muestra primero.</p>
        </div>
    </div>

    @if (isset($question))
        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm transition-colors hover:bg-zinc-50">
            <input type="hidden" name="is_active" value="0">

            <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 accent-zinc-900"
                   @checked((bool) old('is_active', $question->is_active))>

            <span>
                <span class="block font-medium">Pregunta activa</span>

                <span class="block text-xs text-zinc-500">
                    Las preguntas inactivas no se muestran a los operadores.
                </span>
            </span>
        </label>

        @error('is_active')
            <p class="field-error">{{ $message }}</p>
        @enderror
    @endif
</div>
