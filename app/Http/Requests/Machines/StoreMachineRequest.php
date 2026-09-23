<?php

namespace App\Http\Requests\Machines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maquinas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machines', 'name'),
            ],

            'description' => ['nullable', 'string', 'max:1000'],

            'user_ids' => ['sometimes', 'array'],

            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',

            'name.unique' => 'Ya existe una máquina con ese nombre.',

            'user_ids.array' => 'La lista de usuarios enviada no es válida.',
            'user_ids.*.exists' => 'Seleccionaste un usuario que no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'user_ids' => 'usuarios',
            'user_ids.*' => 'usuario',
        ];
    }
}
