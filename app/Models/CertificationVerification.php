<?php

namespace App\Models;

use Database\Factories\CertificationVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A certificate submitted by an actor, waiting for (or after) moderation.
 */
class CertificationVerification extends Model
{
    /** @use HasFactory<CertificationVerificationFactory> */
    use HasFactory;

    protected $fillable = ['actor_id', 'certification_id', 'status', 'document', 'certificate_number', 'expires_at', 'rejection_reason', 'submitted_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }
}
