<?php

namespace App\Http\Requests\Ats;

use App\Models\AtsQuestion;
use App\Models\ShiftRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class FinishShiftRecordRequest extends FormRequest
{
    protected Collection|null $finishQuestions = null;

    public function authorize(): bool
    {
        $user = $this->user();
        $record = $this->route('record');

        return ($user?->can('ats.cerrar_propios') ?? false)
            && $record instanceof ShiftRecord
            && $record->user_id === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->finishQuestions() as $question) {
            $rules["answers.{$question->id}"] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.*.required' => 'Debes responder esta pregunta.',
            'answers.*.boolean' => 'La respuesta seleccionada no es válida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->finishQuestions() as $question) {
            $attributes["answers.{$question->id}"] = $question->title;
        }

        return $attributes;
    }

    /**
     * @return Collection<int, AtsQuestion>
     */
    public function finishQuestions(): Collection
    {
        return $this->finishQuestions ??= AtsQuestion::query()
            ->active()
            ->stage(AtsQuestion::STAGE_FINISH)
            ->ordered()
            ->get();
    }
}
