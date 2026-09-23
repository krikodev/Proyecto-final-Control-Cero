<?php

namespace App\Http\Requests\Ats;

use App\Models\AtsQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAtsQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ats.gestionar') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'stage' => ['required', Rule::in([AtsQuestion::STAGE_START, AtsQuestion::STAGE_FINISH])],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'La pregunta es obligatoria.',
            'title.max' => 'La pregunta no puede superar los 255 caracteres.',
            'stage.required' => 'Debes indicar la etapa de la pregunta.',
            'stage.in' => 'La etapa seleccionada no es válida.',
            'position.integer' => 'El orden debe ser un número.',
            'position.min' => 'El orden no puede ser negativo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'pregunta',
            'stage' => 'etapa',
            'position' => 'orden',
            'is_active' => 'estado',
        ];
    }
}
