<?php

namespace App\Http\Controllers;

use App\Http\Requests\Machines\StoreMachineRequest;
use App\Http\Requests\Machines\UpdateMachineRequest;
use App\Http\Requests\Machines\UpdateMachineStatusRequest;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $query = Machine::query()
            ->withCount('users')
            ->with('users:id,name,last_name')
            ->orderBy('name');

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        $machines = $query->paginate(10)->withQueryString();

        return view('machines.index', compact('machines', 'search'));
    }

    public function create(): View
    {
        return view('machines.create', [
            'users' => $this->assignableUsers(),
        ]);
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $userIds = array_map('intval', $data['user_ids'] ?? []);
        unset($data['user_ids']);

        DB::transaction(function () use ($data, $userIds) {
            $machine = new Machine($data);
            $machine->is_active = true;
            $machine->save();

            $machine->users()->sync($userIds);
        });

        return redirect()
            ->route('machines.index')
            ->with('success', 'Máquina creada correctamente.');
    }

    public function edit(Machine $machine): View
    {
        $machine->load('users:id');

        return view('machines.edit', [
            'machine' => $machine,
            'users' => $this->assignableUsers(),
        ]);
    }

    public function update(UpdateMachineRequest $request, Machine $machine): RedirectResponse
    {
        $data = $request->validated();

        $userIds = array_map('intval', $data['user_ids'] ?? []);
        unset($data['user_ids']);

        // El centinela indica que el checklist fue enviado: si no llega,
        // los usuarios habilitados no se tocan.
        $syncUsers = array_key_exists('sync_users', $data);
        unset($data['sync_users']);

        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : null;
        unset($data['is_active']);

        // Todo o nada: si el cambio de estado está bloqueado
        // (falta maquinas.activar) no se aplica nada.
        DB::transaction(function () use ($machine, $data, $userIds, $syncUsers, $isActive) {
            $machine->update($data);

            if ($syncUsers) {
                $machine->users()->sync($userIds);
            }

            if ($isActive !== null && $isActive !== (bool) $machine->is_active) {
                $machine->is_active = $isActive;
                $machine->save();
            }
        });

        return redirect()
            ->route('machines.index')
            ->with('success', 'Máquina actualizada correctamente.');
    }

    public function updateStatus(
        UpdateMachineStatusRequest $request,
        Machine $machine
    ): RedirectResponse {
        $machine->is_active = $request->boolean('is_active');
        $machine->save();

        return back()->with(
            'success',
            $machine->is_active
                ? 'La máquina está habilitada.'
                : 'La máquina está inactiva.'
        );
    }

    /**
     * Usuarios que pueden habilitarse en una máquina.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function assignableUsers()
    {
        return User::query()
            ->where('is_active', true)
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'email']);
    }
}
