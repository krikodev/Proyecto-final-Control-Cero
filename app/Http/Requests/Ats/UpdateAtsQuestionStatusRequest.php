<?php

namespace App\Http\Requests\Ats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAtsQuestionStatusRequest extends FormRequest
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
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.required' => 'Debes indicar el estado de la pregunta.',
            'is_active.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'is_active' => 'estado',
        ];
    }
}
