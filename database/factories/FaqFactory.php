<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => 'Comment fonctionne '.fake()->randomElement(['la traçabilité', 'l\'éco-score', 'la modération']).' ?',
            'answer' => 'Chaque information affichée est fournie par les acteurs de la chaîne puis vérifiée par l\'équipe NutriTrace.',
            'position' => fake()->numberBetween(1, 50),
        ];
    }
}
