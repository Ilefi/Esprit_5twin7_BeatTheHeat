<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportEvidence extends Model
{
    protected $table = 'report_evidence';

    protected $fillable = ['report_id', 'kind', 'name', 'size', 'url'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
