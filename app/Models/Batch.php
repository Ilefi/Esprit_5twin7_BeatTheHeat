<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A traced production lot (e.g. NT-2026-OLV-0412) and its ordered steps.
 */
class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    protected $fillable = ['code', 'product_id', 'quantity', 'status', 'production_date'];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(BatchStep::class)->orderBy('position');
    }

    protected function totalKm(): Attribute
    {
        return Attribute::get(fn () => (int) $this->steps->sum('distance_km'));
    }

    protected function actorsCount(): Attribute
    {
        return Attribute::get(fn () => $this->steps->pluck('actor_id')->filter()->unique()->count());
    }
}
