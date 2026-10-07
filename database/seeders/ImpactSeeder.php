<?php

namespace Database\Seeders;

use App\Models\Impact;
use App\Models\Product;
use App\Support\EcoScore;
use Illuminate\Database\Seeder;

class ImpactSeeder extends Seeder
{
    /**
     * eco_points and eco_score are computed by the Impact model (App\Support\EcoScore).
     */
    public function run(): void
    {
        $products = Product::pluck('id', 'slug');

        // product => [co2/kg, water/kg, km, packaging, seasonal, methodology, source, breakdown % (EcoScore::BREAKDOWN_STAGES order)]
        $impacts = [
            'huile-olive-sfax' => [2.4, 1100, 280, 'recyclable', true, 'lca', 'ACV simplifiée de la coopérative, campagne 2025 (exemple)', [62, 21, 9, 8]],
            'dattes-deglet-nour-tozeur' => [1.1, 2200, 430, 'compostable', true, 'measured', 'Relevés de la station de conditionnement (exemple)', [48, 6, 38, 8]],
            'harissa-cap-bon' => [1.6, 600, 70, 'recyclable', false, 'estimated', null, [40, 35, 5, 20]],
            'oranges-maltaises-nabeul' => [0.4, 450, 65, 'compostable', true, 'measured', 'Compteurs d\'irrigation du verger (exemple)', [70, 0, 22, 8]],
            'miel-thym-zaghouan' => [1.2, 200, 60, 'recyclable', false, 'declared', null, [55, 10, 5, 30]],
            'figues-djebba' => [0.6, 900, 120, 'compostable', true, 'declared', null, [72, 0, 20, 8]],
            'amandes-sfax' => [2.1, 8000, 280, 'mixed', false, 'lca', 'Étude ACV de la filière amande (exemple)', [58, 14, 10, 18]],
            'couscous-complet' => [1.4, 1600, 150, 'plastic', false, 'estimated', null, [52, 28, 8, 12]],
            'pois-chiches-beja' => [0.9, 1200, 210, 'plastic', false, 'estimated', null, [61, 10, 13, 16]],
            'tomates-sechees' => [3.8, 1900, 900, 'mixed', false, 'estimated', null, [38, 30, 22, 10]],
            'fromage-chevre-zaghouan' => [8.5, 3500, 180, 'plastic', true, 'lca', 'Bilan carbone de la fromagerie (exemple)', [80, 8, 4, 8]],
            'sel-marin-sfax' => [0.3, 10, 1800, 'recyclable', false, 'declared', null, [20, 5, 70, 5]],
        ];

        foreach (array_keys($impacts) as $i => $slug) {
            [$co2, $water, $km, $packaging, $seasonal, $methodology, $source, $breakdown] = $impacts[$slug];

            Impact::factory()->create([
                'product_id' => $products[$slug],
                'co2_per_kg' => $co2,
                'water_per_kg' => $water,
                'distance_km' => $km,
                'packaging' => $packaging,
                'seasonal' => $seasonal,
                'methodology' => $methodology,
                'source' => $source,
                'breakdown' => array_combine(EcoScore::BREAKDOWN_STAGES, $breakdown),
                'updated_at' => now()->subDays(3 * ($i + 1)),
            ]);
        }
    }
}
