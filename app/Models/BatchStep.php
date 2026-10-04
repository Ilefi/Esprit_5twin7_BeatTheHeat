<?php

namespace App\Models;

use Database\Factories\BatchStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchStep extends Model
{
    /** @use HasFactory<BatchStepFactory> */
    use HasFactory;

    protected $fillable = ['batch_id', 'actor_id', 'position', 'stage', 'title', 'location', 'date', 'action', 'documents', 'distance_km', 'verified'];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'documents' => 'array',
            'verified' => 'boolean',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}
