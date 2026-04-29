<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@micropago.bo'],
            [
                'name' => 'Admin MicroPago',
                'password' => bcrypt('admin1234'),
                'role' => 'admin',
                'ci' => '00000000',
                'date_of_birth' => '1990-01-01',
                'balance' => 0.00,
                'nfc_card_uid' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'pasajero@micropago.bo'],
            [
                'name' => 'Pasajero Demo',
                'password' => bcrypt('demo1234'),
                'role' => 'passenger',
                'ci' => '12345678',
                'date_of_birth' => '2000-06-15',
                'balance' => 50.00,
                'nfc_card_uid' => 'DEMO-NFC-TAG-001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'conductor@micropago.bo'],
            [
                'name' => 'Conductor Demo',
                'password' => bcrypt('demo1234'),
                'role' => 'driver',
                'ci' => '87654321',
                'date_of_birth' => '1985-03-20',
                'balance' => 0.00,
                'nfc_card_uid' => null,
            ]
        );
    }
}
