<?php

namespace App\Http\Requests\Users;

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
        $user = $this->route('usuario');

        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:100'],

            'dni' => [
                'required',
                'string',
                'regex:/^[0-9]{8}$/',
                Rule::unique('users', 'dni')->ignore($user),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],


            'role_id' => ['prohibited'],
            'is_active' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'required_with' => 'Completa el campo :attribute.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',
            'prohibited' => 'No puedes modificar :attribute desde este formulario.',

            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Ese DNI pertenece a otro usuario.',

            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo pertenece a otro usuario.',

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
            'is_active' => 'estado',
        ];
    }
}
