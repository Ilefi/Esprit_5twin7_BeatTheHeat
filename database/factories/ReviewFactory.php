<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /** rating => [title, body] candidates */
    private const TEXTS = [
        5 => [
            ['Excellent produit', 'Goût authentique et traçabilité complète, je recommande sans hésiter.'],
            ['Parfait', 'Qualité constante d\'une commande à l\'autre, on sent le travail du producteur.'],
            ['Un vrai régal', 'Produit savoureux, et j\'apprécie de pouvoir suivre chaque étape du lot.'],
        ],
        4 => [
            ['Très bon', 'Très bonne qualité, seul le prix est un peu élevé.'],
            ['Bon rapport qualité/prix', 'Produit sérieux et bien emballé, livraison rapide.'],
            ['Satisfait', 'Bon produit, j\'aimerais un emballage un peu plus écologique.'],
        ],
        3 => [
            ['Correct', 'Produit correct sans plus, la fiche est très complète en revanche.'],
            ['Moyen', 'Qualité variable selon les lots, à surveiller.'],
        ],
        2 => [
            ['Déçu', 'Le produit ne correspond pas tout à fait à la description affichée.'],
        ],
        1 => [
            ['À éviter', 'Produit abîmé à la réception et aucune réponse du vendeur.'],
        ],
    ];

    public function definition(): array
    {
        $rating = fake()->randomElement([5, 5, 5, 4, 4, 4, 3, 3, 2, 1]);
        [$title, $body] = fake()->randomElement(self::TEXTS[$rating]);
        $subRating = fn () => max(1, min(5, $rating + fake()->numberBetween(-1, 1)));
        $createdAt = fake()->dateTimeBetween('-12 months');

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => $rating,
            'quality_rating' => $subRating(),
            'transparency_rating' => $subRating(),
            'value_rating' => $subRating(),
            'title' => $title,
            'body' => $body,
            'verified_purchase' => fake()->boolean(70),
            'helpful_count' => fake()->numberBetween(0, 25),
            'status' => 'published',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'flagged']);
    }
}
