<?php

namespace App\Http\Controllers;
use App\Http\Requests\Users\StoreUserRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Services\Users\UpdateUserStatusService;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role:id,name')
            ->latest('id')
            ->paginate(10, [
                'id',
                'name',
                'last_name',
                'dni',
                'email',
                'role_id',
                'is_active',
            ]);

        return view('users.index', compact('users'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $authUser = $request->user();

        $users = User::query()
            ->with('role:id,name')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('dni', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhereHas('role', function ($rq) use ($query) {
                      $rq->where('name', 'like', "%{$query}%");
                  });
            })
            ->latest('id')
            ->paginate(10, [
                'id',
                'name',
                'last_name',
                'dni',
                'email',
                'role_id',
                'is_active',
            ]);

        $users->getCollection()->transform(function ($user) use ($authUser) {
            $user->can_edit = $authUser->can('usuarios.editar');
            $user->can_activate = $authUser->can('usuarios.activar') && $user->id !== $authUser->id;
            return $user;
        });

        return response()->json($users);
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

        $user = new User();
        $user->name = $data['name'];
        $user->last_name = $data['last_name'];
        $user->dni = $data['dni'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];
        $user->password = Hash::make($data['password']);
        $user->is_active = true;
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }
    public function edit(User $user): View
    {
        $user->load('role:id,name');

        return view('users.edit', compact('user'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user

    ): RedirectResponse {
        $data = $request->validated();

        $user->name = $data['name'];
        $user->last_name = $data['last_name'];
        $user->dni = $data['dni'];
        $user->email = $data['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('users.index', $user)
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