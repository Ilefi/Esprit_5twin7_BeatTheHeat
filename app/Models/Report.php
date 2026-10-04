<?php

namespace App\Models;

use App\View\Components\StatusBadge;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A citizen report about a product, an actor or a certification (polymorphic "reportable").
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public const OPEN_STATUSES = ['pending', 'in_review'];

    public const CLOSED_STATUSES = ['confirmed', 'rejected', 'resolved'];

    protected $fillable = [
        'ref', 'type', 'reportable_type', 'reportable_id', 'title', 'description',
        'status', 'priority', 'reporter_id', 'assignee_id', 'resolution',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Report $report) => $report->ref ??= self::nextRef());
    }

    /** Next tracking reference, e.g. SIG-2026-0017. */
    public static function nextRef(): string
    {
        return sprintf('SIG-%d-%04d', now()->year, (self::max('id') ?? 0) + 1);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ReportMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ReportNote::class)->orderBy('created_at')->orderBy('id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ReportEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', self::OPEN_STATUSES);
    }

    /**
     * Eager loads the reported record and what $report->target needs.
     */
    public function scopeWithTarget(Builder $query): void
    {
        $query->with(['reportable' => fn (MorphTo $morph) => $morph->morphWith([Product::class => ['category', 'producer']])]);
    }

    /**
     * Display data for the reported record: type, type_label, id, name, subtitle, icon, url.
     */
    protected function target(): Attribute
    {
        return Attribute::get(function () {
            $record = $this->reportable;

            [$subtitle, $icon, $url] = match ($this->reportable_type) {
                'product' => [$record->producer->name, $record->category->icon, route('front.products.show', $record->slug)],
                'actor' => [$record->city, 'fa-industry', route('front.actors.show', $record->slug)],
                'certification' => [$record->issuer, 'fa-award', route('front.certifications.show', $record->slug)],
            };

            return (object) [
                'type' => $this->reportable_type,
                'type_label' => StatusBadge::labelFor('report_target', $this->reportable_type),
                'id' => $record->id,
                'name' => $record->name,
                'subtitle' => $subtitle,
                'icon' => $icon,
                'url' => $url,
            ];
        });
    }

    protected function openDays(): Attribute
    {
        return Attribute::get(fn () => (int) $this->created_at->diffInDays(now()));
    }
}
