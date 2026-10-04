<?php

namespace Database\Factories;

use App\Models\Actor;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Huile d\'olive vierge', 'Dattes Allig', 'Grenades de Gabès', 'Piments séchés', 'Pois chiches',
            'Miel de romarin', 'Câpres au sel', 'Pistaches de Kasserine', 'Thé à la menthe', 'Lentilles vertes',
        ]);

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999999),
            'name' => $name,
            'category_id' => Category::factory(),
            'producer_id' => Actor::factory()->producer(),
            'processor_id' => null,
            'region' => fake()->randomElement(['Sfax', 'Tozeur', 'Nabeul', 'Béja', 'Zaghouan', 'Gabès', 'Kairouan']),
            'format' => fake()->randomElement(['Sachet kraft 250 g', 'Bocal verre 200 g', 'Bouteille verre 50 cl', 'Barquette carton 500 g']),
            'price' => fake()->randomFloat(2, 2, 40),
            'description' => 'Produit cultivé et conditionné en Tunisie, avec une traçabilité complète du champ au rayon.',
            'composition' => $name.' 100 %.',
            'image' => null,
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }
}
