<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts (password: "password"). The first three are the documented logins.
     */
    public function run(): void
    {
        $users = [
            ['Amira Ben Salah', 'admin@nutritrace.tn', 'admin'],
            ['Karim Trabelsi', 'actor@nutritrace.tn', 'actor'],
            ['Yasmine Bouaziz', 'consumer@nutritrace.tn', 'consumer'],
            ['Mehdi Gharbi', 'mehdi.g@example.tn', 'consumer'],
            ['Salma Jebali', 'salma.j@example.tn', 'consumer'],
            ['Nizar Hammami', 'nizar.h@example.tn', 'actor'],
            ['Ines Mansouri', 'ines.m@example.tn', 'consumer'],
            ['Walid Chaabane', 'walid.c@example.tn', 'consumer'],
            ['Rim Ferchichi', 'rim.f@example.tn', 'admin'],
            ['Oussama Belhadj', 'oussama.b@example.tn', 'consumer'],
        ];

        foreach ($users as $i => [$name, $email, $role]) {
            User::factory()->create([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'created_at' => now()->subDays(300 - ($i + 1) * 23),
                'last_login_at' => now()->subHours(($i + 1) * 7),
            ]);
        }

        // Extra consumers who write the generated reviews and reports.
        User::factory(15)->create();
    }
}
