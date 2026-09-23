<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\Users\UpdateUserStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $query = User::query()->with('roles:id,name');

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('dni', 'like', $like);
            });
        }

        $users = $query->paginate(7)->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $roles = Role::query()
            ->whereIn('slug', ['supervisor', 'operador'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::findOrFail($data['role_id']);
        unset($data['role_id']);

        $user = new User($data);
        $user->is_active = true;
        $user->save();

        $user->assignRole($role);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $usuario->load(['roles:id,name,slug', 'permissions:id,name,title']);

        return view('users.edit', [
            'user' => $usuario,
            'roles' => Role::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('module')
                ->orderBy('title')
                ->get(['id', 'name', 'title', 'module']),
            'permissionsViaRoles' => $usuario->getPermissionsViaRoles()->pluck('id'),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $usuario,
        UpdateUserStatusService $statusService
    ): RedirectResponse {
        $data = $request->validated();

        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        // Un formulario envía siempre strings: convierte a int para que
        // Spatie resuelva los ids por findById y no por findByName.
        $permissionIds = array_map('intval', $data['permissions'] ?? []);
        unset($data['permissions']);

        // El centinela indica que el checklist fue enviado: si no llega,
        // los permisos directos no se tocan.
        $syncPermissions = array_key_exists('sync_permissions', $data);
        unset($data['sync_permissions']);

        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : null;
        unset($data['is_active']);

        // Todo o nada: si el cambio de estado está bloqueado
        // (último administrador / cuenta propia) no se aplica nada.
        DB::transaction(function () use ($request, $usuario, $data, $roleId, $permissionIds, $syncPermissions, $isActive, $statusService) {
            $usuario->update($data);

            if ($roleId !== null) {
                $usuario->syncRoles([Role::findOrFail($roleId)]);
            }

            if ($syncPermissions) {
                $usuario->syncPermissions($permissionIds);
            }

            if ($isActive !== null && $isActive !== (bool) $usuario->is_active) {
                $statusService->handle($request->user(), $usuario, $isActive);
            }
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function updateStatus(
        UpdateUserStatusRequest $request,
        User $user,
        UpdateUserStatusService $service
    ): RedirectResponse {
        $isActive = $request->boolean('is_active');

        $service->handle(
            $request->user(),
            $user,
            $isActive
        );

        return back()->with(
            'success',
            $isActive
            ? 'La cuenta está activa.'
            : 'La cuenta está inactiva.'
        );
    }
}
