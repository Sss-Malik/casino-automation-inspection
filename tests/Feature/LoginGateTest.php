<?php

namespace Tests\Feature;

use Tests\AutomationTestCase;

class LoginGateTest extends AutomationTestCase
{
    public function test_player_with_valid_credentials_cannot_log_in(): void
    {
        $player = $this->player();

        $response = $this->post('/login', [
            'email' => $player->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_can_log_in(): void
    {
        $admin = $this->superAdmin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_cannot_log_in(): void
    {
        $employee = $this->userWithRole('Employee');

        $this->post('/login', ['email' => $employee->email, 'password' => 'secret123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_existing_session_of_non_super_admin_is_rejected(): void
    {
        // Sessions live in the shared DB for 7 days; a player who logged in
        // before the gate shipped must be thrown out on their next request.
        $player = $this->player();

        $response = $this->actingAs($player)->get('/tasks');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_super_admin_session_reaches_protected_pages(): void
    {
        $this->actingAs($this->superAdmin())->get('/tasks')->assertOk();
    }
}
