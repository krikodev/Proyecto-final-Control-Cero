<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Crear una cuenta administradora';

    public function handle(): int
    {
        $role = Role::where('slug', 'administrador')->first();

        if (! $role) {
            $this->error('Primero ejecuta RolesAndPermissionsSeeder.');

            return self::FAILURE;
        }

        $data = [
            'name' => trim((string) $this->ask('Nombres')),
            'last_name' => trim((string) $this->ask('Apellidos')),
            'dni' => trim((string) $this->ask('DNI de 8 dígitos')),
            'email' => strtolower(
                trim((string) $this->ask('Correo electrónico'))
            ),
            'password' => $this->secret('Contraseña: mínimo 12 caracteres'),
            'password_confirmation' => $this->secret('Repite la contraseña'),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:100'],
            'dni' => [
                'required',
                'regex:/^[0-9]{8}$/',
                'unique:users,dni',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'max' => 'El campo :attribute supera el tamaño permitido.',
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Ya existe un usuario con ese DNI.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User();
        $user->name = $data['name'];
        $user->last_name = $data['last_name'];
        $user->dni = $data['dni'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->is_active = true;
        $user->role()->associate($role);
        $user->save();

        $this->info('Cuenta administradora creada correctamente.');

        $this->table(
            ['ID', 'Nombre', 'Rol', 'Estado'],
            [[
                $user->id,
                $user->name . ' ' . $user->last_name,
                $role->name,
                'Activo',
            ]]
        );

        return self::SUCCESS;
    }
}