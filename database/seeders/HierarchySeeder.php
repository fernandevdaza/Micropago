<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tariff;
use App\Models\TransportLine;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HierarchySeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Tarifas ────────────────────────────────────────────────────────
        Tariff::updateOrCreate(['name' => 'General'],      ['price' => 2.80]);
        Tariff::updateOrCreate(['name' => 'Estudiante'],   ['price' => 1.00]);
        Tariff::updateOrCreate(['name' => 'Tercera Edad'], ['price' => 1.00]);

        // ── 2. Super Admin ────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'superadmin@micropago.bo'],
            [
                'name'          => 'Super Admin MicroPago',
                'password'      => Hash::make('password'),
                'role'          => UserRole::SuperAdmin,
                'ci'            => '1000001',
                'date_of_birth' => '1980-01-01',
                'balance'       => 0,
            ]
        );

        // ── 3. Admin de plataforma ────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@micropago.bo'],
            [
                'name'          => 'Admin MicroPago',
                'password'      => Hash::make('password'),
                'role'          => UserRole::Admin,
                'ci'            => '1000002',
                'date_of_birth' => '1982-03-15',
                'balance'       => 0,
            ]
        );

        // ── 4. Línea 121 ──────────────────────────────────────────────────────
        $linea121 = TransportLine::updateOrCreate(
            ['name' => 'Línea 121'],
            ['description' => 'Servicio de transporte urbano zona sur']
        );

        // ── 5. Line Admin de la Línea 121 ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'lineadmin121@micropago.bo'],
            [
                'name'              => 'Admin Línea 121',
                'password'          => Hash::make('password'),
                'role'              => UserRole::LineAdmin,
                'transport_line_id' => $linea121->id,
                'ci'                => '1000003',
                'date_of_birth'     => '1985-05-10',
                'balance'           => 0,
            ]
        );

        // ── 6. Conductor Línea 121 ────────────────────────────────────────────
        $conductor = User::updateOrCreate(
            ['email' => 'conductor121@micropago.bo'],
            [
                'name'              => 'Conductor Línea 121',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Driver,
                'transport_line_id' => $linea121->id,
                'ci'                => '1000004',
                'date_of_birth'     => '1990-10-15',
                'balance'           => 0,
            ]
        );

        // ── 7. Vehículo del conductor ─────────────────────────────────────────
        Vehicle::updateOrCreate(
            ['license_plate' => 'ABC-121'],
            [
                'driver_id'         => $conductor->id,
                'transport_line_id' => $linea121->id,
                'internal_number'   => 10,
            ]
        );

        // ── 8. Pasajeros demo (CI únicos) ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'pasajero@micropago.bo'],
            [
                'name'          => 'Pasajero Demo (adulto)',
                'password'      => Hash::make('password'),
                'role'          => UserRole::Passenger,
                'ci'            => '2000001',
                'date_of_birth' => '1994-06-15',   // ~30 años → tarifa General
                'balance'       => 50.00,
                'nfc_card_uid'  => 'DEMO-NFC-001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'estudiante@micropago.bo'],
            [
                'name'          => 'Pasajero Demo (estudiante)',
                'password'      => Hash::make('password'),
                'role'          => UserRole::Passenger,
                'ci'            => '2000002',
                'date_of_birth' => '2010-03-20',   // ~14 años → tarifa Estudiante
                'balance'       => 50.00,
                'nfc_card_uid'  => 'DEMO-NFC-002',
            ]
        );

        User::updateOrCreate(
            ['email' => 'mayor@micropago.bo'],
            [
                'name'          => 'Pasajero Demo (adulto mayor)',
                'password'      => Hash::make('password'),
                'role'          => UserRole::Passenger,
                'ci'            => '2000003',
                'date_of_birth' => '1955-08-30',   // ~69 años → tarifa Tercera Edad
                'balance'       => 50.00,
                'nfc_card_uid'  => 'DEMO-NFC-003',
            ]
        );
    }
}
