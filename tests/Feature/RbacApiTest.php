<?php

use App\Enums\UserRole;
use App\Models\Tariff;
use App\Models\Transaction;
use App\Models\TransportLine;
use App\Models\User;
use App\Models\Vehicle;
use Laravel\Sanctum\Sanctum;

function makeUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password123',
        'role' => UserRole::Passenger,
        'ci' => fake()->unique()->numerify('########'),
        'date_of_birth' => '1995-01-01',
        'balance' => 0,
        'nfc_card_uid' => null,
        'transport_line_id' => null,
    ], $attributes));
}

it('prevents a line admin from creating passengers or super admins', function () {
    $line = TransportLine::create(['name' => 'Linea 121', 'description' => 'Sur']);
    $lineAdmin = makeUser([
        'role' => UserRole::LineAdmin,
        'transport_line_id' => $line->id,
    ]);

    Sanctum::actingAs($lineAdmin);

    $passengerResponse = $this->postJson('/api/users', [
        'name' => 'Pasajero Bloqueado',
        'email' => 'passenger@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'ci' => '90000001',
        'date_of_birth' => '2001-01-01',
        'role' => 'passenger',
    ]);

    $superAdminResponse = $this->postJson('/api/users', [
        'name' => 'Root Bloqueado',
        'email' => 'root@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'ci' => '90000002',
        'date_of_birth' => '1990-01-01',
        'role' => 'super_admin',
    ]);

    $passengerResponse->assertStatus(403);
    $superAdminResponse->assertStatus(403);
});

it('forces line admin user creation into the same line', function () {
    $lineA = TransportLine::create(['name' => 'Linea A', 'description' => 'Linea A']);
    $lineB = TransportLine::create(['name' => 'Linea B', 'description' => 'Linea B']);
    $lineAdmin = makeUser([
        'role' => UserRole::LineAdmin,
        'transport_line_id' => $lineA->id,
    ]);

    Sanctum::actingAs($lineAdmin);

    $response = $this->postJson('/api/users', [
        'name' => 'Chofer A',
        'email' => 'drivera@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'ci' => '90000003',
        'date_of_birth' => '1994-01-01',
        'role' => 'driver',
        'transport_line_id' => $lineB->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.role', 'driver');

    $created = User::where('email', 'drivera@example.com')->firstOrFail();

    expect($created->transport_line_id)->toBe($lineA->id);
});

it('prevents line admins from creating transport lines', function () {
    $line = TransportLine::create(['name' => 'Linea 121', 'description' => 'Sur']);
    $lineAdmin = makeUser([
        'role' => UserRole::LineAdmin,
        'transport_line_id' => $line->id,
    ]);

    Sanctum::actingAs($lineAdmin);

    $this->postJson('/api/transport-lines', [
        'name' => 'Linea Nueva',
        'description' => 'No permitida',
    ])->assertForbidden();
});

it('allows only the assigned driver vehicle to process a payment', function () {
    Tariff::create(['name' => 'General', 'price' => 2.80]);

    $line = TransportLine::create(['name' => 'Linea 121', 'description' => 'Linea 121']);
    $driver = makeUser([
        'role' => UserRole::Driver,
        'transport_line_id' => $line->id,
    ]);
    $otherDriver = makeUser([
        'role' => UserRole::Driver,
        'transport_line_id' => $line->id,
        'email' => 'otherdriver@example.com',
        'ci' => '90000004',
    ]);
    $passenger = makeUser([
        'role' => UserRole::Passenger,
        'email' => 'passenger-pay@example.com',
        'ci' => '90000005',
        'balance' => 10,
        'nfc_card_uid' => 'NFC-1234',
    ]);

    $vehicle = Vehicle::create([
        'transport_line_id' => $line->id,
        'driver_id' => $driver->id,
        'internal_number' => 10,
        'license_plate' => 'ABC-101',
    ]);
    $otherVehicle = Vehicle::create([
        'transport_line_id' => $line->id,
        'driver_id' => $otherDriver->id,
        'internal_number' => 11,
        'license_plate' => 'ABC-202',
    ]);

    Sanctum::actingAs($driver);

    $this->postJson('/api/pay', [
        'nfc_card_uid' => $passenger->nfc_card_uid,
        'vehicle_id' => $otherVehicle->id,
    ])->assertForbidden();

    $this->postJson('/api/pay', [
        'nfc_card_uid' => $passenger->nfc_card_uid,
        'vehicle_id' => $vehicle->id,
    ])->assertOk();
});

it('allows recharge only to admin and super admin', function () {
    $admin = makeUser([
        'role' => UserRole::Admin,
        'email' => 'admin@example.com',
        'ci' => '90000006',
    ]);
    $line = TransportLine::create(['name' => 'Linea 77', 'description' => 'Linea 77']);
    $lineAdmin = makeUser([
        'role' => UserRole::LineAdmin,
        'transport_line_id' => $line->id,
        'email' => 'lineadmin@example.com',
        'ci' => '90000007',
    ]);
    $passenger = makeUser([
        'role' => UserRole::Passenger,
        'email' => 'wallet@example.com',
        'ci' => '90000008',
        'balance' => 5,
        'nfc_card_uid' => 'NFC-5678',
    ]);

    Sanctum::actingAs($lineAdmin);

    $this->postJson('/api/admin/recharge', [
        'user_id' => $passenger->id,
        'amount' => 10,
    ])->assertForbidden();

    Sanctum::actingAs($admin);

    $this->postJson('/api/admin/recharge', [
        'user_id' => $passenger->id,
        'amount' => 10,
    ])->assertOk();

    expect(Transaction::where('user_id', $passenger->id)->where('type', 'recharge')->exists())->toBeTrue();
});
