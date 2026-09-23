<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_change_role_status_and_direct_permissions_of_another_user(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $target = $this->makeActiveUserWithRole('Supervisor');

        $operador = Role::query()->where('slug', 'operador')->firstOrFail();
        $extraPermission = Permission::query()->where('name', 'reportes.ver')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('users.update', $target->id), [
            'name' => $target->name,
            'last_name' => 'Modificado',
            'dni' => $target->dni,
            'email' => $target->email,
            // Los formularios envían strings: se envía igual que un navegador.
            'role_id' => (string) $operador->id,
            'is_active' => '0',
            'sync_permissions' => '1',
            'permissions' => [(string) $extraPermission->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $target->refresh();

        $this->assertTrue($target->hasRole('Operador'));
        $this->assertFalse((bool) $target->is_active);
        $this->assertTrue($target->hasDirectPermission($extraPermission));
        $this->assertSame('Modificado', $target->last_name);
    }

    public function test_clearing_the_checklist_removes_direct_permissions(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $target = $this->makeActiveUserWithRole('Supervisor');

        $permission = Permission::query()->where('name', 'reportes.ver')->firstOrFail();
        $target->givePermissionTo($permission);

        $this->actingAs($admin)->put(route('users.update', $target->id), [
            'name' => $target->name,
            'last_name' => $target->last_name,
            'dni' => $target->dni,
            'email' => $target->email,
            'sync_permissions' => '1',
        ])->assertRedirect(route('users.index'));

        $this->assertFalse($target->fresh()->hasDirectPermission($permission));
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $supervisor = Role::query()->where('slug', 'supervisor')->firstOrFail();

        $this->actingAs($admin)->put(route('users.update', $admin->id), [
            'name' => $admin->name,
            'last_name' => $admin->last_name,
            'dni' => $admin->dni,
            'email' => $admin->email,
            'role_id' => (string) $supervisor->id,
        ])->assertSessionHasErrors('role_id');

        $this->assertTrue($admin->fresh()->hasRole('Administrador'));
    }

    public function test_admin_cannot_deactivate_the_last_active_admin(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        $this->actingAs($admin)->put(route('users.update', $admin->id), [
            'name' => 'Nombre Cambiado',
            'last_name' => $admin->last_name,
            'dni' => $admin->dni,
            'email' => $admin->email,
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');

        $fresh = $admin->fresh();

        // La transacción revierte también el resto del formulario.
        $this->assertTrue((bool) $fresh->is_active);
        $this->assertSame($admin->name, $fresh->name);
    }

    public function test_permissions_checklist_requires_role_management_permission(): void
    {
        $editor = $this->makeActiveUserWithRole('Supervisor');
        // Permiso directo: habilita editar usuarios sin gestionar roles.
        $editor->givePermissionTo('usuarios.editar');

        $target = $this->makeActiveUserWithRole('Operador');
        $permission = Permission::query()->where('name', 'reportes.ver')->firstOrFail();

        $this->actingAs($editor->fresh())
            ->put(route('users.update', $target->id), [
                'name' => $target->name,
                'last_name' => $target->last_name,
                'dni' => $target->dni,
                'email' => $target->email,
                'sync_permissions' => '1',
                'permissions' => [(string) $permission->id],
            ])
            ->assertSessionHasErrors(['permissions', 'sync_permissions']);

        $this->assertFalse($target->fresh()->hasDirectPermission($permission));
    }

    public function test_edit_form_shows_status_role_and_permission_checklist(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $target = $this->makeActiveUserWithRole('Supervisor');

        $this->actingAs($admin)
            ->get(route('users.edit', $target->id))
            ->assertOk()
            ->assertSee('name="is_active"', false)
            ->assertSee('name="role_id"', false)
            ->assertSee('name="sync_permissions"', false)
            ->assertSee('name="permissions[]"', false)
            ->assertSee('Permisos adicionales')
            ->assertSee('Habilitar cuenta');
    }

    public function test_edit_form_hides_role_selector_for_the_own_account(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        $this->actingAs($admin)
            ->get(route('users.edit', $admin->id))
            ->assertOk()
            ->assertDontSee('name="role_id"', false)
            ->assertSee('Tu propio rol no puede modificarse');
    }

    public function test_supervisor_without_permissions_cannot_open_the_edit_form(): void
    {
        $supervisor = $this->makeActiveUserWithRole('Supervisor');
        $target = $this->makeActiveUserWithRole('Operador');

        $this->actingAs($supervisor)
            ->get(route('users.edit', $target->id))
            ->assertForbidden();
    }

    private function makeActiveUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'last_name' => fake()->lastName(),
            'dni' => fake()->unique()->numerify('########'),
        ]);

        $user->assignRole($role);

        return $user;
    }
}
