<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.crear') ?? false;
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:100'],

            'dni' => [
                'required',
                'string',
                'regex:/^[0-9]{8}$/',
                Rule::unique('users', 'dni'),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(
                    fn ($query) => $query->whereIn(
                        'slug',
                        ['supervisor', 'operador']
                    )
                ),
            ],

            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],

            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',

            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Ya existe un usuario con ese DNI.',

            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ya existe un usuario con ese correo.',

            'role_id.integer' => 'Selecciona un rol válido.',
            'role_id.exists' => 'Selecciona supervisor u operador.',

            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
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
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }
}