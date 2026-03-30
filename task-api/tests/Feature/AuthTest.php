<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Register
    // -------------------------------------------------------------------------

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.email', 'alice@example.com')
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'created_at'],
                     'token',
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_register_returns_422_for_duplicate_email(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_all_fields(): void
    {
        $this->postJson('/api/register', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'bob@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'bob@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.email', 'bob@example.com')
                 ->assertJsonStructure(['data', 'token']);
    }

    public function test_login_returns_422_for_wrong_password(): void
    {
        User::factory()->create(['email' => 'bob@example.com']);

        $this->postJson('/api/login', [
            'email'    => 'bob@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['email']);
    }

    public function test_login_returns_422_for_nonexistent_user(): void
    {
        $this->postJson('/api/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ])->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/logout')
             ->assertStatus(204);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/logout')
             ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Unauthenticated access
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->getJson('/api/user')
             ->assertStatus(200)
             ->assertJsonPath('data.id', $user->id);
    }
}
