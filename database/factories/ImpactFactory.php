<?php

namespace Database\Factories;

use App\Models\Impact;
use App\Models\Product;
use App\Support\EcoScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * eco_points / eco_score are computed by the Impact model when it is saved.
 *
 * @extends Factory<Impact>
 */
class ImpactFactory extends Factory
{
    public function definition(): array
    {
        $production = fake()->numberBetween(30, 75);
        $processing = fake()->numberBetween(0, 100 - $production - 10);
        $transport = fake()->numberBetween(5, 100 - $production - $processing - 5);

        return [
            'product_id' => Product::factory(),
            'co2_per_kg' => fake()->randomFloat(1, 0.2, 9),
            'water_per_kg' => fake()->numberBetween(10, 8000),
            'distance_km' => fake()->numberBetween(20, 1500),
            'packaging' => fake()->randomElement(array_keys(EcoScore::PACKAGING_PENALTIES)),
            'seasonal' => fake()->boolean(),
            'methodology' => fake()->randomElement(array_keys(EcoScore::METHODOLOGIES)),
            'source' => null,
            'breakdown' => array_combine(EcoScore::BREAKDOWN_STAGES, [$production, $processing, $transport, 100 - $production - $processing - $transport]),
        ];
    }
}
