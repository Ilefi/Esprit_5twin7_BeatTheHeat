<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->createMany([
            ['name' => 'Huiles & condiments', 'slug' => 'huiles-condiments', 'icon' => 'fa-bottle-droplet'],
            ['name' => 'Fruits', 'slug' => 'fruits', 'icon' => 'fa-apple-whole'],
            ['name' => 'Fruits secs', 'slug' => 'fruits-secs', 'icon' => 'fa-seedling'],
            ['name' => 'Épicerie & céréales', 'slug' => 'epicerie-cereales', 'icon' => 'fa-wheat-awn'],
            ['name' => 'Produits de la ruche', 'slug' => 'produits-ruche', 'icon' => 'fa-jar'],
            ['name' => 'Produits laitiers', 'slug' => 'produits-laitiers', 'icon' => 'fa-cheese'],
        ]);
    }
}
