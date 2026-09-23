<?php

namespace App\Http\Requests\Machines;

use App\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maquinas.editar') ?? false;
    }

    public function rules(): array
    {
        $machine = $this->route('machine');

        // Habilitar/desactivar máquinas exige maquinas.activar.
        $canToggleStatus = (bool) $this->user()?->can('maquinas.activar');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machines', 'name')->ignore($machine instanceof Machine ? $machine->id : $machine),
            ],

            'description' => ['nullable', 'string', 'max:1000'],

            'user_ids' => ['sometimes', 'array'],

            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id'),
            ],

            // Centinela del checklist: si llega, se sincronizan los usuarios
            // habilitados aunque vengan vacíos (nadie seleccionado).
            'sync_users' => ['sometimes', 'boolean'],

            'is_active' => $canToggleStatus
                ? ['sometimes', 'boolean']
                : ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',
            'prohibited' => 'No tienes permiso para modificar :attribute.',

            'name.unique' => 'Ya existe una máquina con ese nombre.',

            'user_ids.array' => 'La lista de usuarios enviada no es válida.',
            'user_ids.*.exists' => 'Seleccionaste un usuario que no existe.',

            'sync_users.boolean' => 'La lista de usuarios enviada no es válida.',

            'is_active.boolean' => 'El estado seleccionado no es válido.',
            'is_active.prohibited' => 'No tienes permiso para habilitar o desactivar máquinas.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'user_ids' => 'usuarios',
            'user_ids.*' => 'usuario',
            'sync_users' => 'usuarios habilitados',
            'is_active' => 'estado de la máquina',
        ];
    }
}
