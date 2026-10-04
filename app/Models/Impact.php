<?php

namespace App\Models;

use App\Support\EcoScore;
use Database\Factories\ImpactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Environmental footprint of a product. eco_points / eco_score are always derived from the indicators.
 */
class Impact extends Model
{
    /** @use HasFactory<ImpactFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'co2_per_kg', 'water_per_kg', 'distance_km', 'packaging', 'seasonal', 'breakdown'];

    protected function casts(): array
    {
        return [
            'co2_per_kg' => 'float',
            'water_per_kg' => 'float',
            'distance_km' => 'float',
            'seasonal' => 'boolean',
            'eco_points' => 'integer',
            'breakdown' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Impact $impact) {
            $impact->eco_points = EcoScore::points($impact->co2_per_kg, $impact->water_per_kg, $impact->distance_km, $impact->packaging, $impact->seasonal);
            $impact->eco_score = EcoScore::grade($impact->eco_points);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
