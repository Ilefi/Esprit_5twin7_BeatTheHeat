<?php

namespace Database\Factories;

use App\Http\Controllers\Admin\BatchController;
use App\Models\Actor;
use App\Models\Batch;
use App\Models\BatchStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchStep>
 */
class BatchStepFactory extends Factory
{
    public function definition(): array
    {
        $stage = fake()->randomElement(array_keys(BatchController::STAGES));

        return [
            'batch_id' => Batch::factory(),
            'actor_id' => Actor::factory(),
            'position' => 1,
            'stage' => $stage,
            'title' => BatchController::STAGES[$stage],
            'location' => fake()->randomElement(['Sfax', 'Tozeur', 'Nabeul', 'Béja', 'Sousse', 'Tunis']),
            'date' => fake()->dateTimeBetween('-2 months'),
            'action' => 'Étape enregistrée et contrôlée par l\'acteur responsable.',
            'documents' => [],
            'distance_km' => fake()->numberBetween(0, 300),
            'verified' => true,
        ];
    }
}
