<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // off_tag: Open Food Facts taxonomy category used for the external benchmark.
        Category::factory()->createMany([
            ['name' => 'Huiles & condiments', 'slug' => 'huiles-condiments', 'icon' => 'fa-bottle-droplet', 'off_tag' => 'en:condiments'],
            ['name' => 'Fruits', 'slug' => 'fruits', 'icon' => 'fa-apple-whole', 'off_tag' => 'en:fruits'],
            ['name' => 'Fruits secs', 'slug' => 'fruits-secs', 'icon' => 'fa-seedling', 'off_tag' => 'en:dried-fruits'],
            ['name' => 'Épicerie & céréales', 'slug' => 'epicerie-cereales', 'icon' => 'fa-wheat-awn', 'off_tag' => 'en:cereals-and-potatoes'],
            ['name' => 'Produits de la ruche', 'slug' => 'produits-ruche', 'icon' => 'fa-jar', 'off_tag' => 'en:honeys'],
            ['name' => 'Produits laitiers', 'slug' => 'produits-laitiers', 'icon' => 'fa-cheese', 'off_tag' => 'en:dairies'],
        ]);

        // Categories whose products replace each other in the "Meilleures alternatives" block.
        $categories = Category::pluck('id', 'slug');
        Category::where('slug', 'fruits')->firstOrFail()->syncSubstitutes([$categories['fruits-secs']]);
        Category::where('slug', 'produits-ruche')->firstOrFail()->syncSubstitutes([$categories['fruits-secs']]);
    }
}
