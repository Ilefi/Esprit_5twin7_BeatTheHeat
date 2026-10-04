<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => sprintf('NT-%d-%s-%04d', now()->year, strtoupper(fake()->lexify('???')), fake()->unique()->numberBetween(1, 9999)),
            'product_id' => Product::factory(),
            'quantity' => sprintf('%d unités', fake()->numberBetween(2, 30) * 100),
            'status' => fake()->randomElement(array_keys(StatusBadge::options('batch'))),
            'production_date' => fake()->dateTimeBetween('-3 months', '-1 week'),
        ];
    }
}
