<?php

namespace Database\Factories;

use App\Http\Controllers\Admin\CategoryController;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Légumes', 'Épices', 'Poissons', 'Boissons', 'Confitures', 'Herbes aromatiques', 'Pâtisseries', 'Conserves']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999999),
            'icon' => fake()->randomElement(array_keys(CategoryController::ICONS)),
        ];
    }
}
