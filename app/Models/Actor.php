<?php

namespace App\Models;

use Database\Factories\ActorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A supply-chain actor: producer, processor or distributor.
 */
class Actor extends Model
{
    /** @use HasFactory<ActorFactory> */
    use HasFactory;

    protected $fillable = ['slug', 'name', 'type', 'city', 'region', 'description', 'founded_year', 'verified', 'email', 'phone'];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
        ];
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class);
    }

    /** Products this actor grows or raises. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'producer_id');
    }

    /** Products this actor transforms. */
    public function processedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'processor_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(BatchStep::class);
    }

    /** Batches with at least one step handled by this actor. */
    public function batches(): Builder
    {
        return Batch::whereHas('steps', fn (Builder $query) => $query->where('actor_id', $this->id));
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Adds products_count and batches_count (distinct batches the actor took part in).
     */
    public function scopeWithStats(Builder $query): void
    {
        $query->withCount('products')->addSelect([
            'batches_count' => BatchStep::query()
                ->selectRaw('count(distinct batch_id)')
                ->whereColumn('batch_steps.actor_id', 'actors.id'),
        ]);
    }
}
