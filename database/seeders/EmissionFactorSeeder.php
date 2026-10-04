<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use Illuminate\Database\Seeder;

class EmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            ['Transport routier (camion 19 t)', 'Transport', 'kg CO₂e / t.km', 0.096],
            ['Transport routier réfrigéré', 'Transport', 'kg CO₂e / t.km', 0.132],
            ['Transport maritime (porte-conteneurs)', 'Transport', 'kg CO₂e / t.km', 0.016],
            ['Emballage verre', 'Emballage', 'kg CO₂e / kg', 0.85],
            ['Emballage carton', 'Emballage', 'kg CO₂e / kg', 0.62],
            ['Emballage plastique PET', 'Emballage', 'kg CO₂e / kg', 2.15],
            ['Électricité réseau national', 'Énergie', 'kg CO₂e / kWh', 0.48],
            ['Irrigation goutte-à-goutte', 'Eau', 'L / kg produit', 320],
            ['Engrais azoté de synthèse', 'Intrants', 'kg CO₂e / kg N', 5.6],
            ['Réfrigération (stockage)', 'Énergie', 'kg CO₂e / t.jour', 0.9],
        ];

        foreach ($factors as $i => [$name, $category, $unit, $value]) {
            EmissionFactor::factory()->create([
                'name' => $name,
                'category' => $category,
                'unit' => $unit,
                'value' => $value,
                'updated_at' => now()->subMonths($i + 1),
            ]);
        }
    }
}
