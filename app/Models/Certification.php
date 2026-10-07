<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['slug', 'name', 'short_name', 'type', 'issuer', 'expires_at', 'description', 'criteria', 'guarantees', 'limits'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'criteria' => 'array',
            'guarantees' => 'array',
            'limits' => 'array',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(CertificationVerification::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /** A certification stays valid until the end of its expiry day. */
    protected function isExpired(): Attribute
    {
        return Attribute::get(fn () => $this->expires_at?->lt(today()) ?? false);
    }
}
