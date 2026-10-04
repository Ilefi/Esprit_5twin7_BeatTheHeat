<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->randomLetter().'.',
            'role' => fake()->randomElement(['Consommatrice', 'Consommateur', 'Producteur', 'Distributrice']).', '.fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Bizerte']),
            'quote' => 'Grâce à NutriTrace, je sais enfin d\'où viennent les produits que j\'achète.',
            'rating' => fake()->numberBetween(4, 5),
        ];
    }
}
