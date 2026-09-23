<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/maquinas')->assertRedirect(route('login'));
    }

    public function test_admin_can_open_the_machines_index(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        $this->actingAs($admin)->get('/maquinas')->assertOk();
    }

    public function test_supervisor_can_view_but_not_manage_machines(): void
    {
        $supervisor = $this->makeActiveUserWithRole('Supervisor');

        $this->actingAs($supervisor)->get('/maquinas')->assertOk();
        $this->actingAs($supervisor)->get('/maquinas/create')->assertForbidden();
    }

    public function test_operator_has_no_web_access(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');

        $this->actingAs($operator)->get('/maquinas')->assertRedirect(route('login'));
    }

    public function test_admin_can_create_a_machine_with_enabled_users(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $enabled = $this->makeActiveUserWithRole('Operador');

        $response = $this->actingAs($admin)->post(route('machines.store'), [
            'name' => 'Torno CNC-01',
            'description' => 'Torno CNC de la planta 1',
            // Los formularios envían strings: se envía igual que un navegador.
            'user_ids' => [(string) $enabled->id],
        ]);

        $response->assertRedirect(route('machines.index'));

        $machine = Machine::query()->firstOrFail();

        $this->assertSame('Torno CNC-01', $machine->name);
        $this->assertTrue((bool) $machine->is_active);
        $this->assertTrue($machine->users->contains($enabled->id));
    }

    public function test_duplicate_machine_name_is_rejected(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        Machine::create(['name' => 'Torno CNC-01']);

        $this->actingAs($admin)->post(route('machines.store'), [
            'name' => 'Torno CNC-01',
        ])->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_machine_and_clear_all_enabled_users(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $enabled = $this->makeActiveUserWithRole('Operador');

        $machine = Machine::create(['name' => 'Compresor A']);
        $machine->users()->sync([$enabled->id]);

        $response = $this->actingAs($admin)->put(route('machines.update', $machine), [
            'name' => 'Compresor A-2',
            'description' => 'Reubicado en bodega',
            'sync_users' => '1',
            // Sin user_ids: el centinela vacía la lista de habilitados.
        ]);

        $response->assertRedirect(route('machines.index'));

        $machine->refresh();

        $this->assertSame('Compresor A-2', $machine->name);
        $this->assertSame('Reubicado en bodega', $machine->description);
        $this->assertSame(0, $machine->users()->count());
    }

    public function test_machine_status_change_requires_activation_permission(): void
    {
        $editor = $this->makeActiveUserWithRole('Supervisor');
        // Permiso directo: habilita editar máquinas sin activarlas.
        $editor->givePermissionTo('maquinas.editar');

        $machine = Machine::create(['name' => 'Compresor A']);

        $this->actingAs($editor->fresh())
            ->put(route('machines.update', $machine), [
                'name' => 'Compresor B',
                'description' => 'Intento de cambio de estado',
                'is_active' => '0',
            ])
            ->assertSessionHasErrors('is_active');

        $machine->refresh();

        $this->assertSame('Compresor A', $machine->name);
        $this->assertTrue((bool) $machine->is_active);
    }

    public function test_admin_can_toggle_machine_status_from_the_index(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        $machine = Machine::create(['name' => 'Compresor A']);

        $this->actingAs($admin)
            ->patch(route('machines.status', $machine), ['is_active' => '0'])
            ->assertRedirect();

        $this->assertFalse((bool) $machine->fresh()->is_active);
    }

    public function test_index_search_filters_by_name_or_description(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        Machine::create(['name' => 'Torno CNC-01']);
        Machine::create(['name' => 'Compresor A', 'description' => 'Planta 2']);

        $this->actingAs($admin)
            ->get(route('machines.index', ['q' => 'Torno']))
            ->assertOk()
            ->assertSee('Torno CNC-01')
            ->assertDontSee('Compresor A');
    }

    private function makeActiveUserWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }
}
