<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_administrator_can_open_the_users_index(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');

        $this->actingAs($admin)->get('/usuarios')->assertOk();
    }

    public function test_supervisor_without_permissions_gets_a_403_on_users_index(): void
    {
        $supervisor = $this->makeActiveUserWithRole('Supervisor');

        $this->actingAs($supervisor)->get('/usuarios')->assertForbidden();
    }

    public function test_supervisor_without_permissions_cannot_create_users(): void
    {
        $supervisor = $this->makeActiveUserWithRole('Supervisor');

        $this->actingAs($supervisor)->get('/usuarios/create')->assertForbidden();
    }

    public function test_operator_lands_on_their_record_module(): void
    {
        $operator = $this->makeActiveUserWithRole('Operador');

        $this->actingAs($operator)->get('/dashboard')->assertRedirect(route('ats.mine'));
        $this->actingAs($operator)->get('/usuarios')->assertForbidden();
    }

    public function test_inactive_user_is_logged_out(): void
    {
        $admin = $this->makeActiveUserWithRole('Administrador');
        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin->fresh())->get('/dashboard')->assertRedirect(route('login'));
    }

    private function makeActiveUserWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }
}
