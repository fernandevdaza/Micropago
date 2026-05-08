<?php

namespace Tests\Feature;

use App\Models\Tariff;
use App\Models\Transaction;
use App\Models\TransportLine;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function setupPaymentScenario(float $balance = 20.0): array
    {
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->withBalance($balance)->create();

        return compact('line', 'driver', 'vehicle', 'passenger');
    }

    public function test_successful_payment_deducts_balance_and_creates_transaction(): void
    {
        Tariff::factory()->general()->create(); // General = 2.80
        ['driver' => $driver, 'vehicle' => $vehicle, 'passenger' => $passenger] = $this->setupPaymentScenario(20.0);

        $response = $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('tariff_applied', 'General')
            ->assertJsonPath('amount_paid', 2.80);

        $this->assertEquals(17.20, $passenger->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id'    => $passenger->id,
            'vehicle_id' => $vehicle->id,
            'type'       => 'payment',
            'status'     => 'completed',
        ]);
    }

    public function test_payment_fails_with_insufficient_balance(): void
    {
        Tariff::factory()->general()->create();
        ['driver' => $driver, 'vehicle' => $vehicle, 'passenger' => $passenger] = $this->setupPaymentScenario(1.00);

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertStatus(402);
    }

    public function test_payment_fails_with_unknown_nfc_uid(): void
    {
        Tariff::factory()->general()->create();
        ['driver' => $driver, 'vehicle' => $vehicle] = $this->setupPaymentScenario();

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => 'NONEXISTENT',
            'vehicle_id'   => $vehicle->id,
        ])->assertUnprocessable();
    }

    public function test_driver_cannot_charge_with_another_drivers_vehicle(): void
    {
        Tariff::factory()->general()->create();
        $line        = TransportLine::factory()->create();
        $driver      = User::factory()->driver($line->id)->create();
        $otherDriver = User::factory()->driver($line->id)->create();
        $vehicle     = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $otherVehicle = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $otherDriver->id]);
        $passenger   = User::factory()->passenger()->withBalance(20.0)->create();

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $otherVehicle->id,
        ])->assertForbidden();
    }

    public function test_driver_without_vehicle_cannot_process_payment(): void
    {
        Tariff::factory()->general()->create();
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create(); // sin vehículo
        $passenger = User::factory()->passenger()->withBalance(20.0)->create();

        // Necesitamos un vehicle_id válido cualquiera para pasar validación
        $otherLine    = TransportLine::factory()->create();
        $otherDriver  = User::factory()->driver($otherLine->id)->create();
        $otherVehicle = Vehicle::factory()->create(['transport_line_id' => $otherLine->id, 'driver_id' => $otherDriver->id]);

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $otherVehicle->id,
        ])->assertForbidden();
    }

    public function test_passenger_cannot_process_payment(): void
    {
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->withBalance(20.0)->create();

        $this->actingAs($passenger)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertForbidden();
    }

    // ─── Tarifas por edad ────────────────────────────────────────────────────

    public function test_minor_passenger_gets_student_tariff(): void
    {
        Tariff::factory()->general()->create();
        Tariff::factory()->estudiante()->create();   // 1.50
        Tariff::factory()->terceraEdad()->create();

        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->aged(15)->withBalance(20.0)->create();

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertOk()->assertJsonPath('tariff_applied', 'Estudiante');
    }

    public function test_elderly_passenger_gets_tercera_edad_tariff(): void
    {
        Tariff::factory()->general()->create();
        Tariff::factory()->estudiante()->create();
        Tariff::factory()->terceraEdad()->create();  // 1.00

        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->aged(65)->withBalance(20.0)->create();

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertOk()->assertJsonPath('tariff_applied', 'Tercera Edad');
    }

    public function test_adult_passenger_gets_general_tariff(): void
    {
        Tariff::factory()->general()->create();
        Tariff::factory()->estudiante()->create();
        Tariff::factory()->terceraEdad()->create();

        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->aged(30)->withBalance(20.0)->create();

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertOk()->assertJsonPath('tariff_applied', 'General');
    }

    public function test_payment_fails_with_500_when_tariff_config_missing(): void
    {
        // No se crean tarifas
        ['driver' => $driver, 'vehicle' => $vehicle, 'passenger' => $passenger] = $this->setupPaymentScenario(20.0);

        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertStatus(500);
    }

    public function test_payment_is_atomic_balance_not_deducted_on_failure(): void
    {
        // Sin tarifa → la transacción DB falla → balance no debe cambiar
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $vehicle   = Vehicle::factory()->create(['transport_line_id' => $line->id, 'driver_id' => $driver->id]);
        $passenger = User::factory()->passenger()->withBalance(50.0)->create();

        // Intentar pago sin tarifa configurada (500)
        $this->actingAs($driver)->postJson('/api/pay', [
            'nfc_card_uid' => $passenger->nfc_card_uid,
            'vehicle_id'   => $vehicle->id,
        ])->assertStatus(500);

        $this->assertEquals(50.0, $passenger->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }
}
