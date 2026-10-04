<?php

namespace App\Models;

use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory;

    protected $fillable = ['slug', 'name', 'short_name', 'type', 'issuer', 'description', 'criteria', 'guarantees', 'limits'];

    protected function casts(): array
    {
        return [
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
}
