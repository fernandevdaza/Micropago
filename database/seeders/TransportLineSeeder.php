<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Symfony\Component\Mailer\Transport;

class TransportLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transport = Transport::create([
            'name'=>'05',
            'description'=>'Av Pirai, Av Trinidad, Octavo Anillo'
        ]);
    }
}
