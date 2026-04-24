<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicle = Vehicle::create([
           'transport_line_id' => 1,
           'driver_id' => 2,
           'internal_number' => 5,
           'license_plate_number' => '6089LHR',
        ]);
    }
}
