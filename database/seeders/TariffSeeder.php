<?php

namespace Database\Seeders;

use App\Models\Tariff;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tariff::updateOrCreate(['name' => 'General'], ['price' => 2.80]);
        Tariff::updateOrCreate(['name' => 'Estudiante'], ['price' => 1.50]);
        Tariff::updateOrCreate(['name' => 'Tercera Edad'], ['price' => 0.00]);
    }
}
