<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.editar') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge([
                'email' => strtolower(trim($email)),
            ]);
        }
    }

    public function rules(): array
    {
        $target = $this->route('usuario');
        $actor = $this->user();

        $isSelf = $target instanceof User
            && $actor !== null
            && $target->is($actor);

        // Cambiar roles (incluido "Administrador") o administrar permisos
        // directos exige el permiso roles.gestionar.
        $canManageRoles = (bool) $actor?->can('roles.gestionar');

        // Activar/desactivar cuentas exige usuarios.activar.
        $canToggleStatus = (bool) $actor?->can('usuarios.activar');

        $roleRules = ['sometimes', 'integer'];

        if ($isSelf) {
            $roleRules[] = 'prohibited';
        } elseif ($canManageRoles) {
            $roleRules[] = Rule::exists('roles', 'id');
        } else {
            $roleRules[] = Rule::exists('roles', 'id')->where(
                fn ($query) => $query->whereIn('slug', ['supervisor', 'operador'])
            );
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:100'],

            'dni' => [
                'required',
                'string',
                'regex:/^[0-9]{8}$/',
                Rule::unique('users', 'dni')->ignore($target),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($target),
            ],

            'role_id' => $roleRules,

            'is_active' => $canToggleStatus
                ? ['sometimes', 'boolean']
                : ['prohibited'],

            'permissions' => $canManageRoles
                ? ['sometimes', 'array']
                : ['prohibited'],

            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('guard_name', 'web'),
            ],

            // Centinela del checklist: si llega, se sincronizan los permisos
            // directos aunque vengan vacíos (todavía desmarcados).
            'sync_permissions' => $canManageRoles
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

            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Ese DNI pertenece a otro usuario.',

            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo pertenece a otro usuario.',

            'role_id.integer' => 'Selecciona un rol válido.',
            'role_id.exists' => 'Selecciona un rol válido.',
            'role_id.prohibited' => 'No puedes cambiar el rol de tu propia cuenta.',

            'is_active.boolean' => 'El estado seleccionado no es válido.',
            'is_active.prohibited' => 'No tienes permiso para activar o desactivar cuentas.',

            'permissions.prohibited' => 'No tienes permiso para administrar permisos adicionales.',
            'permissions.array' => 'La lista de permisos enviada no es válida.',
            'permissions.*.exists' => 'Seleccionaste un permiso que no existe.',

            'sync_permissions.prohibited' => 'No tienes permiso para administrar permisos adicionales.',
            'sync_permissions.boolean' => 'La lista de permisos enviada no es válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombres',
            'last_name' => 'apellidos',
            'dni' => 'DNI',
            'email' => 'correo electrónico',
            'role_id' => 'rol',
            'is_active' => 'estado de la cuenta',
            'permissions' => 'permisos',
            'sync_permissions' => 'permisos adicionales',
        ];
    }
}
