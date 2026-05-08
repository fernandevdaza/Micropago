<?php

namespace Tests\Feature;

use App\Models\TransportLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ─── Super Admin ─────────────────────────────────────────────────────────

    public function test_super_admin_can_list_all_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->count(3)->create();

        $this->actingAs($superAdmin)->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_super_admin_can_create_admin_without_line(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->postJson('/api/users', [
            'name'                  => 'Nuevo Admin',
            'email'                 => 'admin@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '10000001',
            'date_of_birth'         => '1985-01-01',
            'role'                  => 'admin',
        ])->assertCreated()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_super_admin_can_create_driver_with_line(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $line       = TransportLine::factory()->create();

        $this->actingAs($superAdmin)->postJson('/api/users', [
            'name'                  => 'Conductor',
            'email'                 => 'driver@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '10000002',
            'date_of_birth'         => '1990-01-01',
            'role'                  => 'driver',
            'transport_line_id'     => $line->id,
        ])->assertCreated()
            ->assertJsonPath('data.role', 'driver');
    }

    public function test_super_admin_cannot_create_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->postJson('/api/users', [
            'name'                  => 'Root',
            'email'                 => 'root@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '10000003',
            'date_of_birth'         => '1980-01-01',
            'role'                  => 'super_admin',
        ])->assertForbidden();
    }

    public function test_super_admin_cannot_update_another_super_admin(): void
    {
        $superAdmin  = User::factory()->superAdmin()->create();
        $superAdmin2 = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->patchJson("/api/users/{$superAdmin2->id}", [
            'name' => 'Nuevo nombre',
        ])->assertForbidden();
    }

    public function test_super_admin_cannot_delete_super_admin(): void
    {
        $superAdmin  = User::factory()->superAdmin()->create();
        $superAdmin2 = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->deleteJson("/api/users/{$superAdmin2->id}")
            ->assertForbidden();
    }

    public function test_super_admin_cannot_self_delete(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->deleteJson("/api/users/{$superAdmin->id}")
            ->assertForbidden();
    }

    // ─── Admin ───────────────────────────────────────────────────────────────

    public function test_admin_can_create_driver(): void
    {
        $admin = User::factory()->admin()->create();
        $line  = TransportLine::factory()->create();

        $this->actingAs($admin)->postJson('/api/users', [
            'name'                  => 'Chofer',
            'email'                 => 'chofer@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '20000001',
            'date_of_birth'         => '1990-01-01',
            'role'                  => 'driver',
            'transport_line_id'     => $line->id,
        ])->assertCreated();
    }

    public function test_admin_cannot_create_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/users', [
            'name'                  => 'Admin2',
            'email'                 => 'admin2@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '20000002',
            'date_of_birth'         => '1985-01-01',
            'role'                  => 'admin',
        ])->assertForbidden();
    }

    public function test_admin_cannot_create_super_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/users', [
            'name'                  => 'Root',
            'email'                 => 'root2@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '20000003',
            'date_of_birth'         => '1985-01-01',
            'role'                  => 'super_admin',
        ])->assertForbidden();
    }

    public function test_admin_cannot_create_passenger(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/users', [
            'name'                  => 'Pasajero',
            'email'                 => 'pass@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '20000004',
            'date_of_birth'         => '1995-01-01',
            'role'                  => 'passenger',
        ])->assertForbidden();
    }

    public function test_admin_can_update_driver(): void
    {
        $admin  = User::factory()->admin()->create();
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($admin)->patchJson("/api/users/{$driver->id}", [
            'name' => 'Nombre Actualizado',
        ])->assertOk()->assertJsonPath('data.name', 'Nombre Actualizado');
    }

    public function test_admin_cannot_update_another_admin(): void
    {
        $admin  = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $this->actingAs($admin)->patchJson("/api/users/{$admin2->id}", [
            'name' => 'Hack',
        ])->assertForbidden();
    }

    public function test_admin_can_delete_driver(): void
    {
        $admin  = User::factory()->admin()->create();
        $driver = User::factory()->driver()->create();

        $this->actingAs($admin)->deleteJson("/api/users/{$driver->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $driver->id]);
    }

    // ─── Line Admin ──────────────────────────────────────────────────────────

    public function test_line_admin_only_sees_users_of_their_line(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        User::factory()->driver($lineA->id)->create();
        User::factory()->driver($lineB->id)->create();

        $response = $this->actingAs($lineAdmin)->getJson('/api/users');
        $response->assertOk();

        // Debe ver solo los de su línea (lineAdmin + 1 driver de lineA)
        foreach ($response->json('data') as $u) {
            $this->assertEquals($lineA->id, $u['transport_line_id']);
        }
    }

    public function test_line_admin_creation_forces_own_line(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        $response = $this->actingAs($lineAdmin)->postJson('/api/users', [
            'name'                  => 'Chofer',
            'email'                 => 'chofer2@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '30000001',
            'date_of_birth'         => '1992-01-01',
            'role'                  => 'driver',
            'transport_line_id'     => $lineB->id, // intenta asignar otra línea
        ])->assertCreated();

        // Debe ser forzado a lineA
        $this->assertEquals($lineA->id, $response->json('data.transport_line_id'));
    }

    public function test_line_admin_cannot_create_admin(): void
    {
        $line      = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($line->id)->create();

        $this->actingAs($lineAdmin)->postJson('/api/users', [
            'name'                  => 'Admin',
            'email'                 => 'newadmin@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '30000002',
            'date_of_birth'         => '1985-01-01',
            'role'                  => 'admin',
        ])->assertForbidden();
    }

    public function test_line_admin_cannot_update_driver_of_another_line(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();
        $driver    = User::factory()->driver($lineB->id)->create();

        $this->actingAs($lineAdmin)->patchJson("/api/users/{$driver->id}", [
            'name' => 'Hack',
        ])->assertForbidden();
    }

    // ─── Passenger / Driver (no acceso) ──────────────────────────────────────

    public function test_passenger_cannot_list_users(): void
    {
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($passenger)->getJson('/api/users')->assertForbidden();
    }

    public function test_driver_cannot_create_users(): void
    {
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($driver)->postJson('/api/users', [
            'name'                  => 'Test',
            'email'                 => 'test@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ci'                    => '40000001',
            'date_of_birth'         => '1990-01-01',
            'role'                  => 'driver',
        ])->assertForbidden();
    }

    public function test_nfc_card_uid_not_exposed_for_non_passenger(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $line       = TransportLine::factory()->create();
        $driver     = User::factory()->driver($line->id)->create();

        $response = $this->actingAs($superAdmin)->getJson("/api/users/{$driver->id}");
        $response->assertOk();

        $this->assertNull($response->json('data.nfc_card_uid'));
    }

    public function test_nfc_card_uid_exposed_to_operator_for_passenger(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $passenger  = User::factory()->passenger()->create();

        // Desde v2: super_admin también puede ver el NFC del pasajero
        $response = $this->actingAs($superAdmin)->getJson("/api/users/{$passenger->id}");
        $response->assertOk();

        $this->assertNotNull($response->json('data.nfc_card_uid'));
    }

    public function test_passenger_sees_own_nfc_via_me_endpoint(): void
    {
        $passenger = User::factory()->passenger()->create();

        $response = $this->actingAs($passenger)->getJson('/api/auth/me');
        $response->assertOk();

        $this->assertNotNull($response->json('data.nfc_card_uid'));
    }
}
