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

     /** Links every traced lot should show, from the farm to the consumer (processing is optional). */
    public const ESSENTIAL_STAGES = ['production', 'distribution', 'consumer'];

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

    /**
     * Traceability completeness, 0-100: essential links present (40), verified steps (40), dates in order (20).
     */
    protected function completeness(): Attribute
    {
        return Attribute::get(function () {
            if ($this->steps->isEmpty()) {
                return 0;
            }

            $present = collect(self::ESSENTIAL_STAGES)->intersect($this->steps->pluck('stage'))->count();

            $actorSteps = $this->steps->whereNotNull('actor_id');
            $verified = $actorSteps->isEmpty() ? 0 : $actorSteps->where('verified', true)->count() / $actorSteps->count();

            $ordered = $this->steps->every(fn ($step, $i) => $i === 0 || ! $step->date->lt($this->steps[$i - 1]->date));

            return (int) round(40 * $present / count(self::ESSENTIAL_STAGES) + 40 * $verified + ($ordered ? 20 : 0));
        });
    }
}
