<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportEvent extends Model
{
    protected $fillable = ['report_id', 'label', 'author', 'created_at'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
