<?php

namespace Database\Seeders;

use App\Models\AtsQuestion;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
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
            'ats.gestionar' => 'Administrar las preguntas del ATS',

            'reportes.ver' => 'Consultar reportes',

            'maquinas.ver' => 'Consultar máquinas',
            'maquinas.crear' => 'Crear máquinas',
            'maquinas.editar' => 'Editar máquinas',
            'maquinas.activar' => 'Activar o desactivar máquinas',
        ];

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
                    'ats.gestionar',
                    'reportes.ver',

                    'maquinas.ver',
                    'maquinas.crear',
                    'maquinas.editar',
                    'maquinas.activar',
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
                    'maquinas.ver',
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

        foreach ($permissions as $slug => $title) {
            Permission::updateOrCreate(
                ['name' => $slug, 'guard_name' => 'web'],
                [
                    'title' => $title,
                    'module' => explode('.', $slug)[0],
                ]
            );
        }

        foreach ($roles as $slug => $data) {
            $role = Role::updateOrCreate(
                ['name' => $data['name'], 'guard_name' => 'web'],
                [
                    'slug' => $slug,
                    'description' => $data['description'],
                ]
            );

            // El seeder es la fuente de verdad de los permisos iniciales.
            $permissionIds = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $data['permissions'])
                ->pluck('id');

            $role->syncPermissions($permissionIds);
        }

        $this->seedAtsQuestions();

        // La caché de Spatie debe vaciarse tras tocar permisos/pivotes.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Preguntas de ejemplo del ATS (el administrador puede editarlas o
     * desactivarlas desde el módulo "Preguntas ATS").
     */
    private function seedAtsQuestions(): void
    {
        $questions = [
            ['stage' => 'start', 'position' => 1, 'title' => '¿El área de trabajo se encuentra despejada y en condiciones seguras?'],
            ['stage' => 'start', 'position' => 2, 'title' => '¿La máquina pasó la inspección visual antes del uso?'],
            ['stage' => 'start', 'position' => 3, 'title' => '¿Se verificó que los resguardos de seguridad estén colocados?'],
            ['stage' => 'start', 'position' => 4, 'title' => '¿El operador conoce el procedimiento de emergencia del área?'],
            ['stage' => 'start', 'position' => 5, 'title' => '¿Se coordinó el inicio de la actividad con el responsable del turno?'],

            ['stage' => 'finish', 'position' => 1, 'title' => '¿Se realizó la limpieza del área de trabajo?'],
            ['stage' => 'finish', 'position' => 2, 'title' => '¿La máquina quedó apagada y en condiciones seguras?'],
            ['stage' => 'finish', 'position' => 3, 'title' => '¿Se reportaron incidentes o condiciones inseguras durante la actividad?'],
            ['stage' => 'finish', 'position' => 4, 'title' => '¿Se devolvió el EPP y las herramientas a su lugar?'],
            ['stage' => 'finish', 'position' => 5, 'title' => '¿Se completó la documentación del turno?'],
        ];

        foreach ($questions as $question) {
            AtsQuestion::updateOrCreate(
                [
                    'stage' => $question['stage'],
                    'title' => $question['title'],
                ],
                ['position' => $question['position'], 'is_active' => true]
            );
        }
    }
}
