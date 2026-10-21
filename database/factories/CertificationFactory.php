<?php

namespace Database\Factories;

use App\Http\Controllers\Admin\CertificationController;
use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(CertificationController::TYPES));
        $name = CertificationController::TYPES[$type].' '.fake()->unique()->numberBetween(2020, 9999);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'short_name' => Str::limit(CertificationController::TYPES[$type], 25, ''),
            'type' => $type,
            'issuer' => 'Organisme '.fake()->lastName().' (exemple)',
            'expires_at' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
            'description' => 'Label attestant le respect d\'un cahier des charges contrôlé chaque année par un organisme tiers.',
            'criteria' => ['Cahier des charges publié', 'Contrôle annuel sur site', 'Traçabilité documentaire'],
            'guarantees' => ['Pratiques contrôlées par un tiers'],
            'limits' => ['Ne mesure pas l\'empreinte carbone du transport'],
        ];
    }
}
