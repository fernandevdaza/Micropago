<?php

namespace Database\Seeders;

use App\Models\Tariff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tariffs = [
            ['name'=>'Regular',
                'price'=>2.80],
            ['name'=>'Estudiante',
                'price'=>1],
            ['name'=>'Tercera Edad',
                'price'=>1],];

        foreach ($tariffs as $tariff) {
            Tariff::create($tariff);
        }
    }
}
