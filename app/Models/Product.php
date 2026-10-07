<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'slug', 'name', 'category_id', 'producer_id', 'processor_id', 'region', 'format', 'price',
        'description', 'composition', 'image', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Actor::class, 'producer_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Actor::class, 'processor_id');
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class);
    }

    public function impact(): HasOne
    {
        return $this->hasOne(Impact::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function firstBatch(): HasOne
    {
        return $this->hasOne(Batch::class)->oldestOfMany();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * Eager loads what product cards and lists display (avoids N+1 queries).
     */
    public function scopeForCards(Builder $query): void
    {
        $query->with(['category', 'producer', 'certifications', 'impact', 'firstBatch'])->withRating();
    }

    /**
     * Adds rating_avg and reviews_count, computed on published reviews only.
     */
    public function scopeWithRating(Builder $query): void
    {
        $published = fn (Builder $reviews) => $reviews->where('status', 'published');

        $query->withCount(['reviews as reviews_count' => $published])
            ->withAvg(['reviews as rating_avg' => $published], 'rating');
    }

    /** Sorts by eco-score points, best first. */
    public function scopeOrderByEcoPoints(Builder $query): void
    {
        $query->orderByDesc(Impact::select('eco_points')->whereColumn('impacts.product_id', 'products.id'));
    }

    protected function ecoScore(): Attribute
    {
        return Attribute::get(fn () => $this->impact?->eco_score);
    }

    protected function batchCode(): Attribute
    {
        return Attribute::get(fn () => $this->firstBatch?->code);
    }

    /** Falls back to a query when the product was loaded without the withRating() scope. */
    protected function ratingAvg(): Attribute
    {
        return Attribute::get(fn ($value, array $attributes) => round((float) (array_key_exists('rating_avg', $attributes)
            ? $value
            : $this->reviews()->where('status', 'published')->avg('rating')), 1));
    }

    protected function reviewsCount(): Attribute
    {
        return Attribute::get(fn ($value, array $attributes) => (int) (array_key_exists('reviews_count', $attributes)
            ? $value
            : $this->reviews()->where('status', 'published')->count()));
    }
}
