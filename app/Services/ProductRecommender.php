<?php

namespace App\Services;

use App\Models\Impact;
use App\Models\Product;
use App\Support\EcoScore;
use Illuminate\Support\Collection;

/**
 * "Meilleures alternatives" and category benchmark for a product.
 *
 * Candidates are the published products of the same category, then of its substitute categories
 * (Category::substitutes). A candidate is kept only when it beats the product on sustainability
 * (eco points) or, when the product has no footprint yet, simply has one.
 */
class ProductRecommender
{
    /** Points out of 100 given to each criterion of the recommendation score. */
    public const WEIGHTS = [
        'eco' => 60,           // eco points (0–100)
        'rating' => 20,        // average published rating (0–5)
        'certifications' => 10, // valid certifications, capped at 3
        'same_category' => 6,
        'same_region' => 4,
    ];

    /**
     * @return Collection<int, Product> each product gets a `recommendation` attribute: ['score' => int, 'reasons' => list<string>]
     */
    public function alternatives(Product $product, int $limit = 3): Collection
    {
        $product->loadMissing(['impact', 'certifications', 'category.substitutes']);
        $categoryIds = $product->category->substitutes->pluck('id')->push($product->category_id);
        $points = $product->impact?->eco_points;

        return Product::published()->forCards()
            ->whereKeyNot($product->id)
            ->whereIn('category_id', $categoryIds)
            ->whereHas('impact', fn ($query) => $query->when($points !== null, fn ($query) => $query->where('eco_points', '>', $points)))
            ->get()
            ->each(fn (Product $candidate) => $candidate->setAttribute('recommendation', [
                'score' => $this->score($candidate, $product),
                'reasons' => $this->reasons($candidate, $product),
            ]))
            ->sortByDesc(fn (Product $candidate) => $candidate->recommendation['score'])
            ->take($limit)
            ->values();
    }

    /**
     * Where the product stands in its category, on the published products that have a footprint.
     *
     * @return array{rank: int, count: int, better_than: int, average_points: int, average_grade: string}|null
     */
    public function categoryBenchmark(Product $product): ?array
    {
        $points = $product->impact?->eco_points;
        if ($points === null) {
            return null;
        }

        $all = Impact::whereHas('product', fn ($query) => $query->published()->where('category_id', $product->category_id))
            ->pluck('eco_points');
        $others = $all->count() - 1;
        if ($others < 1) {
            return null;
        }

        $average = (int) round($all->avg());

        return [
            'rank' => $all->filter(fn ($p) => $p > $points)->count() + 1,
            'count' => $all->count(),
            'better_than' => (int) round($all->filter(fn ($p) => $p < $points)->count() / $others * 100),
            'average_points' => $average,
            'average_grade' => EcoScore::grade($average),
        ];
    }

    /**
     * Eco-score distribution of the published products of a category, compared on the product page with Open Food Facts.
     *
     * @return array<string, int>
     */
    public function categoryGrades(int $categoryId): array
    {
        $counts = Impact::whereHas('product', fn ($query) => $query->published()->where('category_id', $categoryId))
            ->pluck('eco_score')
            ->countBy();

        return collect(['A', 'B', 'C', 'D', 'E'])->mapWithKeys(fn (string $grade) => [$grade => $counts->get($grade, 0)])->all();
    }

    private function score(Product $candidate, Product $product): int
    {
        $w = self::WEIGHTS;
        $validCertifications = $candidate->certifications->reject->is_expired->count();

        return (int) round(
            $candidate->impact->eco_points / 100 * $w['eco']
            + $candidate->rating_avg / 5 * $w['rating']
            + min(3, $validCertifications) / 3 * $w['certifications']
            + ($candidate->category_id === $product->category_id ? $w['same_category'] : 0)
            + ($candidate->region === $product->region ? $w['same_region'] : 0)
        );
    }

    /** @return list<string> short French arguments shown on the alternative card */
    private function reasons(Product $candidate, Product $product): array
    {
        $reasons = [];
        $mine = $product->impact;
        $theirs = $candidate->impact;

        if (! $mine) {
            $reasons[] = "Éco-score {$theirs->eco_score} évalué";
        } elseif ($theirs->eco_score !== $mine->eco_score) {
            $reasons[] = "Éco-score {$theirs->eco_score} au lieu de {$mine->eco_score}";
        } else {
            $reasons[] = '+'.($theirs->eco_points - $mine->eco_points).' pts d\'éco-score';
        }

        if ($mine && $mine->co2_per_kg > 0 && $theirs->co2_per_kg < $mine->co2_per_kg) {
            $reasons[] = '−'.round((1 - $theirs->co2_per_kg / $mine->co2_per_kg) * 100).' % de CO₂';
        }

        if ($mine && $theirs->distance_km < $mine->distance_km) {
            $reasons[] = round($mine->distance_km - $theirs->distance_km).' km de transport en moins';
        }

        $newLabels = $candidate->certifications->reject->is_expired->pluck('name')
            ->diff($product->certifications->pluck('name'));
        if ($newLabels->isNotEmpty()) {
            $reasons[] = 'Certifié '.$newLabels->first();
        }

        if ($candidate->region === $product->region) {
            $reasons[] = 'Même région ('.$candidate->region.')';
        }

        return array_slice($reasons, 0, 3);
    }
}
