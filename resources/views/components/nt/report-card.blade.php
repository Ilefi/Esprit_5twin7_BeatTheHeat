{{-- Report summary. :anonymized="true" on public pages (Observatoire) hides the reporter. --}}
@props(['report', 'href' => null, 'anonymized' => false])

<article {{ $attributes->class(['nt-card relative flex flex-col p-5', 'nt-card-hover' => $href]) }}>
    <div class="flex flex-wrap items-center gap-2">
        <x-status-badge type="report_type" :value="$report->type" />
        <x-status-badge type="report" :value="$report->status" />
        <span class="ms-auto font-mono text-xs text-muted-foreground">{{ $report->ref }}</span>
    </div>

    <h3 class="mt-3 text-base font-semibold leading-snug">
        @if ($href)
            <a href="{{ $href }}" class="after:absolute after:inset-0 hover:text-primary-strong">{{ $report->title }}</a>
        @else
            {{ $report->title }}
        @endif
    </h3>

    <p class="mt-2 flex items-center gap-2 text-sm text-muted-foreground">
        <i class="fa-solid {{ $report->target->icon }} text-xs" aria-hidden="true"></i>
        {{ $report->target->type_label }} : <span class="font-medium text-foreground">{{ $report->target->name }}</span>
    </p>

    @if ($anonymized && $report->resolution)
        <div class="mt-4 rounded-lg bg-muted/60 p-3 text-sm">
            <p class="font-semibold">Issue</p>
            <p class="text-muted-foreground">{{ $report->resolution }}</p>
        </div>
    @endif

    <p class="mt-auto flex items-center justify-between gap-2 pt-4 text-xs text-muted-foreground">
        <span>{{ $anonymized ? 'Signalé par un consommateur' : 'Par '.$report->reporter->name }}</span>
        <time datetime="{{ $report->created_at->toIso8601String() }}">{{ $report->created_at->translatedFormat('d M Y') }}</time>
    </p>
</article>
