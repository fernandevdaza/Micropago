<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Register ────────────────────────────────────────────────────────────

    public function test_register_creates_passenger_with_nfc(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Juan Pérez',
            'email'                 => 'juan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '12345678',
            'date_of_birth'         => '1995-05-10',
        ]);

        // Auth responses devuelven 'user' (sin wrap data), no 'data'
        $response->assertCreated()
            ->assertJsonPath('user.role', 'passenger')
            ->assertJsonStructure(['user' => ['nfc_card_uid'], 'token']);

        $this->assertNotNull(User::where('email', 'juan@example.com')->value('nfc_card_uid'));
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Otro',
            'email'                 => 'dup@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '99999999',
            'date_of_birth'         => '1990-01-01',
        ])->assertUnprocessable();
    }

    public function test_register_fails_with_duplicate_ci(): void
    {
        User::factory()->create(['ci' => '11111111']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Otro',
            'email'                 => 'otro@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '11111111',
            'date_of_birth'         => '1990-01-01',
        ])->assertUnprocessable();
    }

    public function test_register_fails_without_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', [
            'name'          => 'Test',
            'email'         => 'test@example.com',
            'password'      => 'password123',
            'ci'            => '22222222',
            'date_of_birth' => '1990-01-01',
        ])->assertUnprocessable();
    }

    public function test_register_fails_with_future_birth_date(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '33333333',
            'date_of_birth'         => now()->addYear()->format('Y-m-d'),
        ])->assertUnprocessable();
    }

    public function test_register_fails_with_today_birth_date(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '44444444',
            'date_of_birth'         => now()->format('Y-m-d'),
        ])->assertUnprocessable();
    }

    // ─── Login ───────────────────────────────────────────────────────────────

    public function test_login_returns_token_on_valid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'secret123']);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'secret123',
            // Auth responses usan 'user', no 'data'
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'correct']);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    // ─── Me ──────────────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function test_logout_deletes_token_from_database(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        // Verificar directamente en DB que el token fue eliminado
        $tokenId = explode('|', $token)[0];
        $this->assertNull(PersonalAccessToken::find($tokenId));
    }

    // ─── Refresh ─────────────────────────────────────────────────────────────

    public function test_refresh_issues_new_token_and_deletes_old(): void
    {
        $user     = User::factory()->create();
        $oldToken = $user->createToken('test')->plainTextToken;
        $oldId    = explode('|', $oldToken)[0];

        $response = $this->withToken($oldToken)->postJson('/api/auth/refresh');
        $response->assertOk()->assertJsonStructure(['token']);

        $newToken = $response->json('token');
        $this->assertNotEquals($oldToken, $newToken);

        // Token viejo eliminado de DB
        $this->assertNull(PersonalAccessToken::find($oldId));

        // Token nuevo existe en DB
        $newId = explode('|', $newToken)[0];
        $this->assertNotNull(PersonalAccessToken::find($newId));
    }
}
