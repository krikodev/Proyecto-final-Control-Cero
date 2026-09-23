<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La raíz redirige al invitado hacia el login.
     */
    public function test_the_root_redirects_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_the_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }
}
