<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [];
        $recentUsers = collect();

        if (Gate::allows('usuarios.ver')) {
            $stats[] = [
                'label' => 'Usuarios registrados',
                'value' => User::count(),
            ];

            $stats[] = [
                'label' => 'Cuentas activas',
                'value' => User::where('is_active', true)->count(),
            ];

            $recentUsers = User::query()
                ->with('role:id,name')
                ->latest('id')
                ->limit(5)
                ->get([
                    'id',
                    'name',
                    'last_name',
                    'role_id',
                    'is_active',
                ]);
        }

        if (Gate::allows('roles.gestionar')) {
            $stats[] = [
                'label' => 'Roles configurados',
                'value' => Role::count(),
            ];
        }

        return view('dashboard', compact('stats', 'recentUsers'));
    }
}