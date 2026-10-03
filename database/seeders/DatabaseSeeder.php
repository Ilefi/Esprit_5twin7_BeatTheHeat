<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo accounts, password: "password" (UserFactory default).
        foreach ([
            ['Amira Ben Salah', 'admin@nutritrace.tn', 'admin'],
            ['Karim Trabelsi', 'actor@nutritrace.tn', 'actor'],
            ['Yasmine Bouaziz', 'consumer@nutritrace.tn', 'consumer'],
        ] as [$name, $email, $role]) {
            User::factory()->create(['name' => $name, 'email' => $email, 'role' => $role]);
        }
    }
}
