<?php

namespace Tests\Feature;

use App\Models\Tariff;
use App\Models\Transaction;
use App\Models\TransportLine;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function createTransaction(User $passenger, Vehicle $vehicle, Tariff $tariff): Transaction
    {
        return Transaction::create([
            'user_id'    => $passenger->id,
            'vehicle_id' => $vehicle->id,
            'tariff_id'  => $tariff->id,
            'type'       => 'payment',
            'amount'     => $tariff->price,
            'status'     => 'completed',
        ]);
    }

    public function test_passenger_only_sees_own_transactions(): void
    {
        $line       = TransportLine::factory()->create();
        $driver     = User::factory()->driver($line->id)->create();
        $vehicle    = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $tariff     = Tariff::factory()->general()->create();

        $passenger1 = User::factory()->passenger()->create();
        $passenger2 = User::factory()->passenger()->create();

        $this->createTransaction($passenger1, $vehicle, $tariff);
        $this->createTransaction($passenger2, $vehicle, $tariff);

        $response = $this->actingAs($passenger1)->getJson('/api/transactions');
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals($passenger1->id, $response->json('data.0.user_id'));
    }

    public function test_driver_only_sees_own_vehicle_transactions(): void
    {
        $line     = TransportLine::factory()->create();
        $driver1  = User::factory()->driver($line->id)->create();
        $driver2  = User::factory()->driver($line->id)->create();
        $vehicle1 = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver1->id]);
        $vehicle2 = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver2->id]);
        $tariff   = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();

        $this->createTransaction($passenger, $vehicle1, $tariff);
        $this->createTransaction($passenger, $vehicle2, $tariff);

        $response = $this->actingAs($driver1)->getJson('/api/transactions');
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals($vehicle1->id, $response->json('data.0.vehicle_id'));
    }

    public function test_driver_without_vehicle_gets_404(): void
    {
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create(); // sin vehículo

        $this->actingAs($driver)->getJson('/api/transactions')->assertNotFound();
    }

    public function test_line_admin_sees_only_own_line_transactions(): void
    {
        $lineA    = TransportLine::factory()->create();
        $lineB    = TransportLine::factory()->create();
        $driverA  = User::factory()->driver($lineA->id)->create();
        $driverB  = User::factory()->driver($lineB->id)->create();
        $vehicleA = Vehicle::factory()->create(['transport_line_id' => $lineA->id, 'driver_id' => $driverA->id]);
        $vehicleB = Vehicle::factory()->create(['transport_line_id' => $lineB->id, 'driver_id' => $driverB->id]);
        $tariff   = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();

        $this->createTransaction($passenger, $vehicleA, $tariff);
        $this->createTransaction($passenger, $vehicleB, $tariff);

        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        $response = $this->actingAs($lineAdmin)->getJson('/api/transactions');
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals($vehicleA->id, $response->json('data.0.vehicle_id'));
    }

    public function test_admin_sees_all_transactions(): void
    {
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $tariff    = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();

        $this->createTransaction($passenger, $vehicle, $tariff);
        $this->createTransaction($passenger, $vehicle, $tariff);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // ─── show ────────────────────────────────────────────────────────────────

    public function test_passenger_can_view_own_transaction(): void
    {
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $tariff    = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();

        $tx = $this->createTransaction($passenger, $vehicle, $tariff);

        $this->actingAs($passenger)->getJson("/api/transactions/{$tx->id}")->assertOk();
    }

    public function test_passenger_cannot_view_other_passengers_transaction(): void
    {
        $line       = TransportLine::factory()->create();
        $driver     = User::factory()->driver($line->id)->create();
        $vehicle    = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $tariff     = Tariff::factory()->general()->create();
        $passenger1 = User::factory()->passenger()->create();
        $passenger2 = User::factory()->passenger()->create();

        $tx = $this->createTransaction($passenger1, $vehicle, $tariff);

        $this->actingAs($passenger2)->getJson("/api/transactions/{$tx->id}")->assertForbidden();
    }

    public function test_driver_cannot_view_transaction_from_another_vehicle(): void
    {
        $line     = TransportLine::factory()->create();
        $driver1  = User::factory()->driver($line->id)->create();
        $driver2  = User::factory()->driver($line->id)->create();
        $vehicle1 = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver1->id]);
        $vehicle2 = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver2->id]);
        $tariff   = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();

        $tx = $this->createTransaction($passenger, $vehicle1, $tariff);

        $this->actingAs($driver2)->getJson("/api/transactions/{$tx->id}")->assertForbidden();
    }

    public function test_line_admin_cannot_view_transaction_outside_own_line(): void
    {
        $lineA     = TransportLine::factory()->create();
        $lineB     = TransportLine::factory()->create();
        $driverB   = User::factory()->driver($lineB->id)->create();
        $vehicleB  = Vehicle::factory()->create(['transport_line_id' => $lineB->id, 'driver_id' => $driverB->id]);
        $tariff    = Tariff::factory()->general()->create();
        $passenger = User::factory()->passenger()->create();
        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        $tx = $this->createTransaction($passenger, $vehicleB, $tariff);

        $this->actingAs($lineAdmin)->getJson("/api/transactions/{$tx->id}")->assertForbidden();
    }
}
