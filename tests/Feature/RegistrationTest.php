<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_assigns_the_external_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', [
            'email' => 'cliente@example.com',
            'role' => User::ROLE_EXTERNO,
        ]);
    }

    public function test_public_registration_rejects_privileged_roles(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Usuario no autorizado',
            'email' => 'jefe@example.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'role' => User::ROLE_JEFE,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'jefe@example.com',
        ]);
    }
}
