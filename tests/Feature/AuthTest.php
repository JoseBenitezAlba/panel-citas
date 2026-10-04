<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/citas')->assertRedirect('/login');
    }

    public function test_user_can_register_with_default_role(): void
    {
        $this->post('/registro', [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertRedirect('/citas');

        $this->assertAuthenticated();
        $this->assertSame('user', User::first()->role);
    }

    public function test_registration_cannot_set_role(): void
    {
        $this->post('/registro', [
            'name' => 'Mal', 'email' => 'mal@example.com', 'role' => 'admin',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ]);

        $this->assertSame('user', User::first()->role);
    }

    public function test_login_and_logout(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])->assertRedirect('/citas');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->from('/login')->post('/login', ['email' => 'ana@example.com', 'password' => 'mala'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
