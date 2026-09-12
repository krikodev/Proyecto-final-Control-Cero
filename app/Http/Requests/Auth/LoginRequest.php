<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.string' => 'El correo debe ser un texto válido.',
            'email.email' => 'Ingresa un correo válido.',
            'email.max' => 'El correo es demasiado largo.',
            'password.required' => 'Ingresa tu contraseña.',
            'password.string' => 'La contraseña debe ser un texto válido.',
        ];
    }

    public function authenticate(): void
    {
        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Intenta en {$seconds} segundos.",
            ]);
        }

        $credentials = $this->safe()->only(['email', 'password']);

        $credentials['is_active'] = true;

        $credentials[] = function ($query) {
            $query->whereHas('role');
        };

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'No pudimos iniciar sesión. Revisa tus datos o consulta al administrador.',
            ]);
        }

        RateLimiter::clear($key);
    }

    private function throttleKey(): string
    {
        return 'login:' . hash(
            'sha256',
            $this->validated('email') . '|' . $this->ip()
        );
    }
}