<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewEvent extends Model
{
    protected $fillable = ['review_id', 'label', 'author', 'note', 'created_at'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
