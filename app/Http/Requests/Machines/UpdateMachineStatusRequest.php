<?php

namespace App\Http\Requests\Machines;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maquinas.activar') ?? false;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.required' => 'Debes indicar el estado de la máquina.',
            'is_active.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'is_active' => 'estado de la máquina',
        ];
    }
}
