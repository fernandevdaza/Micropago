<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Enums\UserRole;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [['name' => 'Ferando Daza',
            'email'=>'fernandodev@gmail.com',
            'password' => 'fernando123',
            'role' => UserRole::Admin,
            'ci'=>'9102342',
            'date_of_birth'=>'2006-01-01',
            'balance'=>100,
            'nfc_card_uid'=>'UNKNOWN-ADMIN-001'],
            ['name' => 'Marco Quispe',
            'email'=>'marcosquispe@gmail.com',
            'password' => 'marcosquispe123',
            'role' => UserRole::Driver,
            'ci'=>'6845657',
            'date_of_birth'=>'1993-01-01',
            'balance'=>200,
            'nfc_card_uid'=>'UNKNOWN-DRIVER-001']];


        foreach ($users as $user) {
            User::create($user);
        }
    }
}
