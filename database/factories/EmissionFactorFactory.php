<?php

namespace Database\Factories;

use App\Models\EmissionFactor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmissionFactor>
 */
class EmissionFactorFactory extends Factory
{
    public function definition(): array
    {
        [$name, $category, $unit] = fake()->randomElement([
            ['Transport ferroviaire', 'Transport', 'kg CO₂e / t.km'],
            ['Emballage aluminium', 'Emballage', 'kg CO₂e / kg'],
            ['Chauffage au gaz naturel', 'Énergie', 'kg CO₂e / kWh'],
            ['Irrigation par aspersion', 'Eau', 'L / kg produit'],
        ]);

        return [
            'name' => $name,
            'category' => $category,
            'unit' => $unit,
            'value' => fake()->randomFloat(3, 0.01, 10),
            'source' => 'Valeur indicative — exemple pédagogique',
        ];
    }
}
