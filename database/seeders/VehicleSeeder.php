<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $driver = User::where('role', 'driver')->first();

        if (! $driver) {
            return;
        }

        Vehicle::updateOrCreate(
            ['license_plate' => '6089LHR'],
            [
                'transport_line_id' => 1,
                'driver_id' => $driver->id,
                'internal_number' => 5,
            ]
        );
    }
}
