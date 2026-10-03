{{-- KPI tile. trend: +12 / -3 (percent). :trend-good="false" when a rise is bad news (e.g. open reports). --}}
@props(['icon', 'value', 'label', 'trend' => null, 'trendGood' => true, 'tone' => 'primary', 'href' => null])

@php
    $tones = [
        'primary' => 'bg-primary/12 text-primary-strong',
        'gold' => 'bg-gold/25 text-gold-strong',
        'earth' => 'bg-earth/12 text-earth',
        'danger' => 'bg-danger/10 text-danger-strong',
        'info' => 'bg-info/12 text-info-strong',
        'warning' => 'bg-warning/15 text-warning-strong',
    ];
    $positive = $trend !== null && $trend >= 0;
    $isGood = $positive === (bool) $trendGood;
@endphp

<div {{ $attributes->class(['nt-card relative p-5', 'nt-card-hover' => $href]) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="grid h-11 w-11 place-items-center rounded-lg text-lg {{ $tones[$tone] ?? $tones['primary'] }}">
            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
        </span>
        @if ($trend !== null)
            <span @class(['nt-badge', 'nt-badge-success' => $isGood, 'nt-badge-danger' => ! $isGood])>
                <i @class(['fa-solid text-[0.7em]', 'fa-arrow-trend-up' => $positive, 'fa-arrow-trend-down' => ! $positive]) aria-hidden="true"></i>
                <span class="sr-only">{{ $positive ? 'En hausse de' : 'En baisse de' }}</span>{{ $positive ? '+' : '' }}{{ $trend }} %
            </span>
        @endif
    </div>
    <p class="mt-4 font-heading text-3xl font-bold tracking-tight">{{ $value }}</p>
    <p class="mt-1 text-sm text-muted-foreground">
        @if ($href)
            <a href="{{ $href }}" class="after:absolute after:inset-0">{{ $label }}</a>
        @else
            {{ $label }}
        @endif
    </p>
    {{ $slot }}
</div>
