<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Usuarios iniciales del sistema: 1 administrador, 1 supervisor y 10 operadores.
     *
     * Idempotente: se puede re-ejecutar sin duplicar cuentas (clave: email).
     * Requiere que RolesAndPermissionsSeeder ya haya creado los roles.
     */
    public function run(): void
    {
        $roles = $this->resolveRoles();

        $this->seedUser(
            email: 'admin@controlcero.com',
            name: 'Carlos',
            lastName: 'Administrador',
            dni: '72345618',
            role: $roles['administrador'],
        );

        $this->seedUser(
            email: 'supervisor@controlcero.com',
            name: 'María',
            lastName: 'Supervisora',
            dni: '73456129',
            role: $roles['supervisor'],
        );

        for ($i = 1; $i <= 10; $i++) {
            $this->seedOperator($i, $roles['operador']);
        }
    }

    /**
     * Los 3 roles deben existir antes de asignarlos (se crea en RolesAndPermissionsSeeder).
     *
     * @return array<string, Role>
     */
    private function resolveRoles(): array
    {
        $slugs = ['administrador', 'supervisor', 'operador'];
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        $missing = array_diff($slugs, $roles->keys()->all());

        if ($missing !== []) {
            throw new RuntimeException(
                'Faltan roles: '.implode(', ', $missing).'. Ejecuta primero: php artisan db:seed --class=RolesAndPermissionsSeeder'
            );
        }

        return $roles->all();
    }

    private function seedUser(string $email, string $name, string $lastName, string $dni, Role $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'last_name' => $lastName,
                'dni' => $dni,
                'password' => 'Password123!',
                'email_verified_at' => now(),
            ]
        );

        // is_active no está en $fillable: se asigna fuera de la masa.
        if (! $user->is_active) {
            $user->is_active = true;
            $user->save();
        }

        $user->assignRole($role);
    }

    private function seedOperator(int $index, Role $role): void
    {
        // Email fijo por índice para que updateOrCreate no duplique operadores.
        $this->seedUser(
            email: sprintf('operador%02d@controlcero.com', $index),
            name: fake()->firstName(),
            lastName: fake()->lastName(),
            dni: $this->uniqueDni(),
            role: $role,
        );
    }

    /**
     * DNI de 8 dígitos que no choque con usuarios ya existentes.
     */
    private function uniqueDni(): string
    {
        do {
            $dni = fake()->numerify('########');
        } while (User::query()->where('dni', $dni)->exists());

        return $dni;
    }
}
