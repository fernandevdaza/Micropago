<?php

namespace Database\Seeders;

use App\Models\TransportLine;
use Illuminate\Database\Seeder;

class TransportLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransportLine::updateOrCreate(
            ['name' => '05'],
            ['description' => 'Av Pirai, Av Trinidad, Octavo Anillo']
        );
    }
}
