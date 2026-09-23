<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Los operadores no administran el sistema: aterrizan directamente
     * en su módulo de registro EPP/ATS (donde ven sus máquinas).
     */
    public function index(): View|RedirectResponse
    {
        if (Gate::allows('ats.ver_propios') && ! Gate::allows('usuarios.ver')) {
            return redirect()->route('ats.mine');
        }

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
                ->with('roles:id,name')
                ->latest('id')
                ->limit(5)
                ->get([
                    'id',
                    'name',
                    'last_name',
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