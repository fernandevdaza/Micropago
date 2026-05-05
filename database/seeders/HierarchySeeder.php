<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\TransportLine;
use App\Models\Tariff;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HierarchySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar Tarifas
        Tariff::updateOrCreate(['name' => 'General'], ['price' => 2.00]);
        Tariff::updateOrCreate(['name' => 'Estudiante'], ['price' => 1.00]);
        Tariff::updateOrCreate(['name' => 'Tercera Edad'], ['price' => 1.00]);

        // 2. Super Admin (Dueño de la App)
        User::updateOrCreate(
            ['email' => 'superadmin@micropago.bo'],
            [
                'name' => 'Super Admin MicroPago',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'ci' => '1111111',
                'date_of_birth' => '1980-01-01',
                'balance' => 0,
            ]
        );

        // 3. Crear Línea 121
        $linea121 = TransportLine::updateOrCreate(
            ['name' => 'Línea 121'],
            ['description' => 'Servicio de transporte urbano zona sur']
        );

        // 4. Admin de la Línea 121 (Dueño de Línea)
        $admin121 = User::updateOrCreate(
            ['email' => 'admin121@micropago.bo'],
            [
                'name' => 'Admin Línea 121',
                'password' => Hash::make('password'),
                'role' => UserRole::LineAdmin,
                'transport_line_id' => $linea121->id,
                'ci' => '2222222',
                'date_of_birth' => '1985-05-10',
                'balance' => 0,
            ]
        );

        // 5. Conductor para la Línea 121
        $conductor = User::updateOrCreate(
            ['email' => 'conductor121@micropago.bo'],
            [
                'name' => 'Conductor de Prueba 121',
                'password' => Hash::make('password'),
                'role' => UserRole::Driver,
                'transport_line_id' => $linea121->id,
                'ci' => '3333333',
                'date_of_birth' => '1990-10-15',
                'balance' => 0,
            ]
        );

        // 6. Asignar Vehículo al conductor
        Vehicle::updateOrCreate(
            ['license_plate' => 'ABC-121'],
            [
                'driver_id' => $conductor->id,
                'transport_line_id' => $linea121->id,
                'internal_number' => 10,
            ]
        );

        // 7. Pasajero Demo
        User::updateOrCreate(
            ['email' => 'pasajero@micropago.bo'],
            [
                'name' => 'Pasajero Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Passenger,
                'ci' => '4444444',
                'date_of_birth' => '2000-01-01',
                'balance' => 50.00,
                'nfc_card_uid' => 'DEMO-NFC-001'
            ]
        );
    }
}
