<?php

namespace Database\Factories;

use App\Models\Actor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Actor>
 */
class ActorFactory extends Factory
{
    /** Name prefix per actor type. */
    private const PREFIXES = [
        'producer' => ['Ferme', 'Domaine', 'Oasis', 'Vergers'],
        'processor' => ['Huilerie', 'Atelier', 'Moulin', 'Conserverie'],
        'distributor' => ['Épicerie', 'Coopérative', 'Marché', 'Comptoir'],
    ];

    /** city => governorate */
    private const CITIES = [
        'Sfax' => 'Sfax', 'Tozeur' => 'Tozeur', 'Nabeul' => 'Nabeul', 'Béja' => 'Béja', 'Sousse' => 'Sousse',
        'Bizerte' => 'Bizerte', 'Kairouan' => 'Kairouan', 'Gabès' => 'Gabès', 'Zaghouan' => 'Zaghouan', 'Tunis' => 'Tunis',
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(self::PREFIXES));
        $name = fake()->randomElement(self::PREFIXES[$type]).' '.fake()->lastName();
        $slug = Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999999);
        $city = fake()->randomElement(array_keys(self::CITIES));

        return [
            'slug' => $slug,
            'name' => $name,
            'type' => $type,
            'city' => $city,
            'region' => self::CITIES[$city],
            'description' => 'Acteur engagé dans une démarche de transparence, de la parcelle au point de vente.',
            'founded_year' => fake()->numberBetween(1975, 2022),
            'verified' => fake()->boolean(80),
            'email' => 'contact@'.$slug.'.tn.example',
            'phone' => '+216 7'.fake()->numerify('# ### ###'),
        ];
    }

    public function producer(): static
    {
        return $this->ofType('producer');
    }

    public function processor(): static
    {
        return $this->ofType('processor');
    }

    public function distributor(): static
    {
        return $this->ofType('distributor');
    }

    private function ofType(string $type): static
    {
        return $this->state(function (array $attributes) use ($type) {
            $name = fake()->randomElement(self::PREFIXES[$type]).' '.fake()->lastName();
            $slug = Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999999);

            return ['type' => $type, 'name' => $name, 'slug' => $slug, 'email' => 'contact@'.$slug.'.tn.example'];
        });
    }
}
