<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'rating', 'quality_rating', 'transparency_rating', 'value_rating',
        'title', 'body', 'verified_purchase', 'helpful_count', 'status', 'reply_body', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_purchase' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(ReviewEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /** Statuses that need a moderator. */
    public function scopeAwaitingModeration(Builder $query): void
    {
        $query->whereIn('status', ['pending', 'flagged']);
    }

    /**
     * The producer's public answer, or null.
     */
    protected function reply(): Attribute
    {
        return Attribute::get(fn () => $this->reply_body === null ? null : (object) [
            'author' => $this->product->producer->name,
            'body' => $this->reply_body,
            'created_at' => $this->replied_at,
        ]);
    }
}
