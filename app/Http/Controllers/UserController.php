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
        $users = User::paginate(7);

        return view('users.index', compact('users'));
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

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }
    public function edit(string $id): View
    {
        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, string $id): RedirectResponse
    {
        $data = $request->validated();

        $user = User::findOrFail($id);

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function updateStatus(UpdateUserStatusRequest $request,User $user,UpdateUserStatusService $service): RedirectResponse
    {
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
