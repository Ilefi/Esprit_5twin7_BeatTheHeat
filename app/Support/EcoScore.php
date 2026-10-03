<?php

namespace App\Support;

/**
 * Eco-score A → E computed from impact indicators.
 * The same thresholds are mirrored in resources/js/nutritrace/components.js (ntEcoPreview).
 */
class EcoScore
{
    public const PACKAGING_PENALTIES = [
        'compostable' => 0,
        'recyclable' => 5,
        'mixed' => 12,
        'plastic' => 18,
    ];

    public const PACKAGING_LABELS = [
        'compostable' => 'Compostable',
        'recyclable' => 'Recyclable',
        'mixed' => 'Mixte',
        'plastic' => 'Plastique',
    ];

    public static function points(float $co2PerKg, float $waterPerKg, float $distanceKm, string $packaging, bool $seasonal): int
    {
        $points = 100;
        $points -= min(40, $co2PerKg * 6);
        $points -= min(20, $waterPerKg / 150);
        $points -= min(20, $distanceKm / 100);
        $points -= self::PACKAGING_PENALTIES[$packaging] ?? 10;
        $points += $seasonal ? 5 : -5;

        return (int) max(0, min(100, round($points)));
    }

    public static function grade(int $points): string
    {
        return match (true) {
            $points >= 80 => 'A',
            $points >= 65 => 'B',
            $points >= 50 => 'C',
            $points >= 35 => 'D',
            default => 'E',
        };
    }

    /**
     * @return array<string, array{label: string, range: string, description: string}>
     */
    public static function grades(): array
    {
        return [
            'A' => ['label' => 'Très faible impact', 'range' => '80 – 100 pts', 'description' => 'Produit local et de saison, peu transformé, emballage minimal.'],
            'B' => ['label' => 'Faible impact', 'range' => '65 – 79 pts', 'description' => 'Bon choix au quotidien, quelques postes à améliorer.'],
            'C' => ['label' => 'Impact modéré', 'range' => '50 – 64 pts', 'description' => 'Impact moyen : transport, eau ou emballage pèsent dans la balance.'],
            'D' => ['label' => 'Impact élevé', 'range' => '35 – 49 pts', 'description' => 'À consommer avec modération ou à remplacer par une alternative.'],
            'E' => ['label' => 'Très fort impact', 'range' => '0 – 34 pts', 'description' => 'Fortes émissions ou forte consommation de ressources.'],
        ];
    }
}
