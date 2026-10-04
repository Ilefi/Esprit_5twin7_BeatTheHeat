<?php

namespace Database\Factories;

use App\Models\Actor;
use App\Models\Certification;
use App\Models\CertificationVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationVerification>
 */
class CertificationVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'actor_id' => Actor::factory(),
            'certification_id' => Certification::factory(),
            'status' => 'pending',
            'document' => 'certificat-'.fake()->numerify('####').'.pdf',
            'certificate_number' => 'CERT-'.fake()->unique()->numerify('#####-####'),
            'expires_at' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'rejection_reason' => null,
            'submitted_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'approved']);
    }

    public function rejected(string $reason = 'Certificat expiré.'): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'rejected', 'rejection_reason' => $reason]);
    }
}
