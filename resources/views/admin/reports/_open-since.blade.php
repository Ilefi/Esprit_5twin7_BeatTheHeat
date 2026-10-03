{{-- "Ouvert depuis X jours" indicator for open reports. --}}
@if (in_array($report->status, ['pending', 'in_review'], true))
    <span @class(['inline-flex items-center gap-1 text-xs font-medium', 'text-danger-strong' => $report->open_days > 7, 'text-warning-strong' => $report->open_days > 3 && $report->open_days <= 7, 'text-muted-foreground' => $report->open_days <= 3])>
        <i class="fa-regular fa-clock" aria-hidden="true"></i>
        Ouvert depuis {{ $report->open_days === 0 ? 'aujourd\'hui' : $report->open_days.' j' }}
    </span>
@else
    <span class="text-xs text-muted-foreground">Clôturé {{ $report->updated_at->diffForHumans() }}</span>
@endif
