@forelse ($questions as $index => $question)
    <fieldset class="border-t border-zinc-100 px-6 py-4 first:border-t-0">
        <legend class="sr-only">{{ $question->title }}</legend>

        <p class="text-sm font-medium leading-relaxed">
            {{ $index + 1 }}. {{ $question->title }}
        </p>

        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['1' => 'Sí', '0' => 'No'] as $value => $label)
                @php($old = old("answers.{$question->id}"))

                <label class="inline-flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3.5 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 has-[:checked]:border-zinc-900 has-[:checked]:bg-zinc-900 has-[:checked]:text-white">
                    <input type="radio"
                           name="answers[{{ $question->id }}]"
                           value="{{ $value }}"
                           class="h-4 w-4 accent-zinc-900"
                           @checked($old !== null && (string) $old === $value)>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        @error("answers.{$question->id}")
            <p class="field-error">{{ $message }}</p>
        @enderror
    </fieldset>
@empty
    <p class="px-6 py-6 text-sm text-zinc-500">
        No hay preguntas configuradas para esta etapa todavía.
    </p>
@endforelse
