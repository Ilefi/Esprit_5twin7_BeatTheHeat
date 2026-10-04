<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMessage extends Model
{
    protected $fillable = ['report_id', 'user_id', 'role', 'body', 'attachments', 'created_at'];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
