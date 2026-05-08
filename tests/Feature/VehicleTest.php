<?php

namespace Tests\Feature;

use App\Models\TransportLine;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(int $lineId, int $driverId, array $overrides = []): array
    {
        return array_merge([
            'transport_line_id' => $lineId,
            'driver_id'         => $driverId,
            'internal_number'   => 42,
            'license_plate'     => 'TST-001',
        ], $overrides);
    }

    public function test_admin_can_create_vehicle_with_valid_driver(): void
    {
        $admin  = User::factory()->admin()->create();
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($admin)
            ->postJson('/api/vehicles', $this->validPayload($line->id, $driver->id))
            ->assertCreated();
    }

    public function test_fails_if_driver_already_has_vehicle(): void
    {
        $admin  = User::factory()->admin()->create();
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        Vehicle::factory()->create([
            'transport_line_id' => $line->id,
            'driver_id'         => $driver->id,
        ]);

        $this->actingAs($admin)
            ->postJson('/api/vehicles', $this->validPayload($line->id, $driver->id, [
                'license_plate' => 'NEW-001',
                'internal_number' => 99,
            ]))
            ->assertUnprocessable();
    }

    public function test_fails_if_driver_belongs_to_different_line(): void
    {
        $admin   = User::factory()->admin()->create();
        $lineA   = TransportLine::factory()->create();
        $lineB   = TransportLine::factory()->create();
        $driver  = User::factory()->driver($lineB->id)->create(); // driver de lineB

        $this->actingAs($admin)
            ->postJson('/api/vehicles', $this->validPayload($lineA->id, $driver->id))
            ->assertUnprocessable();
    }

    public function test_fails_if_assigned_user_is_not_driver(): void
    {
        $admin     = User::factory()->admin()->create();
        $line      = TransportLine::factory()->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($admin)
            ->postJson('/api/vehicles', $this->validPayload($line->id, $passenger->id))
            ->assertUnprocessable();
    }

    public function test_line_admin_vehicle_creation_forces_own_line(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();
        $driver    = User::factory()->driver($lineA->id)->create();

        $response = $this->actingAs($lineAdmin)
            ->postJson('/api/vehicles', $this->validPayload($lineB->id, $driver->id)); // intenta lineB

        $response->assertCreated();

        // Verificar en DB que se forzó la línea del line_admin
        $vehicleId = $response->json('data.id');
        $this->assertDatabaseHas('vehicles', [
            'id'                => $vehicleId,
            'transport_line_id' => $lineA->id,
        ]);
    }

    public function test_line_admin_only_sees_own_line_vehicles(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        $driverA = User::factory()->driver($lineA->id)->create();
        $driverB = User::factory()->driver($lineB->id)->create();

        Vehicle::factory()->create(['transport_line_id' => $lineA->id, 'driver_id' => $driverA->id]);
        Vehicle::factory()->create(['transport_line_id' => $lineB->id, 'driver_id' => $driverB->id]);

        $this->actingAs($lineAdmin)->getJson('/api/vehicles')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_driver_cannot_list_vehicles(): void
    {
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($driver)->getJson('/api/vehicles')->assertForbidden();
    }

    public function test_passenger_cannot_create_vehicle(): void
    {
        $passenger = User::factory()->passenger()->create();
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();

        $this->actingAs($passenger)
            ->postJson('/api/vehicles', $this->validPayload($line->id, $driver->id))
            ->assertForbidden();
    }

    public function test_admin_can_delete_vehicle(): void
    {
        $admin   = User::factory()->admin()->create();
        $line    = TransportLine::factory()->create();
        $driver  = User::factory()->driver($line->id)->create();
        $vehicle = Vehicle::factory()->create([
            'transport_line_id' => $line->id,
            'driver_id'         => $driver->id,
        ]);

        $this->actingAs($admin)->deleteJson("/api/vehicles/{$vehicle->id}")
            ->assertNoContent();
    }
}
