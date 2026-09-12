<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permissions = [
                'usuarios.ver' => 'Consultar usuarios',
                'usuarios.crear' => 'Crear usuarios',
                'usuarios.editar' => 'Editar usuarios',
                'usuarios.activar' => 'Activar o desactivar usuarios',

                'roles.gestionar' => 'Administrar roles y sus permisos',

                'equipos.ver' => 'Consultar equipos',
                'equipos.crear' => 'Crear equipos',
                'equipos.editar' => 'Editar equipos',
                'equipos.activar' => 'Activar o desactivar equipos',

                'habilitaciones.gestionar' => 'Habilitar operadores por equipo',

                'ats.ver_propios' => 'Consultar sus propios ATS',
                'ats.ver_todos' => 'Consultar todos los ATS',
                'ats.crear' => 'Registrar apertura de ATS',
                'ats.cerrar_propios' => 'Cerrar sus propios ATS',
                'ats.revisar' => 'Revisar y firmar ATS como responsable',

                'reportes.ver' => 'Consultar reportes',
            ];

            foreach ($permissions as $slug => $name) {
                Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'module' => explode('.', $slug)[0],
                    ]
                );
            }

            $roles = [
                'administrador' => [
                    'name' => 'Administrador',
                    'description' => 'Administra el sistema y supervisa los registros.',
                    'permissions' => [
                        'usuarios.ver',
                        'usuarios.crear',
                        'usuarios.editar',
                        'usuarios.activar',
                        'roles.gestionar',
                        'equipos.ver',
                        'equipos.crear',
                        'equipos.editar',
                        'equipos.activar',
                        'habilitaciones.gestionar',
                        'ats.ver_todos',
                        'ats.revisar',
                        'reportes.ver',
                    ],
                ],

                'supervisor' => [
                    'name' => 'Supervisor',
                    'description' => 'Revisa los ATS y consulta reportes.',
                    'permissions' => [
                        'equipos.ver',
                        'ats.ver_todos',
                        'ats.revisar',
                        'reportes.ver',
                    ],
                ],

                'operador' => [
                    'name' => 'Operador',
                    'description' => 'Registra la apertura y el cierre de sus ATS.',
                    'permissions' => [
                        'equipos.ver',
                        'ats.ver_propios',
                        'ats.crear',
                        'ats.cerrar_propios',
                    ],
                ],
            ];

            foreach ($roles as $slug => $data) {
                $role = Role::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $data['name'],
                        'description' => $data['description'],
                    ]
                );

                // Las asignaciones iniciales se aplican solo al crear el rol.
                if ($role->wasRecentlyCreated) {
                    $permissionIds = Permission::query()
                        ->whereIn('slug', $data['permissions'])
                        ->pluck('id');

                    $role->permissions()->sync($permissionIds);
                }
            }
        });
    }
}