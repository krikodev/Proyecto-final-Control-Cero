<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateUserStatusService
{
    public function handle(
        User $actor,
        User $target,
        bool $isActive
    ): void {
        DB::transaction(function () use ($actor, $target, $isActive) {
            // Serializa los cambios de estado para proteger
            // la existencia de un administrador activo.
            $adminRole = Role::query()
                ->where('slug', 'administrador')
                ->lockForUpdate()
                ->firstOrFail();

            // Revisa nuevamente al responsable dentro de la transacción.
            $currentActor = User::query()
                ->lockForUpdate()
                ->findOrFail($actor->id);

            abort_unless($currentActor->canAccessWeb(), 403);

            Gate::forUser($currentActor)->authorize('usuarios.activar');

            $account = User::query()
                ->lockForUpdate()
                ->findOrFail($target->id);

            // Repetir la misma solicitud no invierte el estado.
            if ((bool) $account->is_active === $isActive) {
                return;
            }

            if (! $isActive) {
                if ($account->hasRole($adminRole)) {
                    $activeAdmins = User::query()
                        ->where('is_active', true)
                        ->whereHas('roles', fn ($query) => $query->where('roles.id', $adminRole->id))
                        ->lockForUpdate()
                        ->get(['id']);

                    if ($activeAdmins->count() <= 1) {
                        throw ValidationException::withMessages([
                            'is_active' => 'No puedes desactivar al último administrador activo.',
                        ]);
                    }
                }

                if ($account->is($currentActor)) {
                    throw ValidationException::withMessages([
                        'is_active' => 'No puedes desactivar tu propia cuenta.',
                    ]);
                }
            }

            $account->is_active = $isActive;
            $account->save();
        }, 3);
    }
}
