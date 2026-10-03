@props(['icon' => 'fa-circle', 'title', 'time' => null, 'tone' => 'primary', 'verified' => false])

@php
    $tones = [
        'primary' => 'bg-primary text-primary-foreground',
        'gold' => 'bg-gold text-gold-foreground',
        'earth' => 'bg-earth text-earth-foreground',
        'danger' => 'bg-danger text-danger-foreground',
        'muted' => 'bg-muted text-muted-foreground ring-1 ring-border',
    ];
@endphp

<li {{ $attributes->class(['relative ps-8']) }}>
    <span class="absolute -start-[17px] top-0 grid h-8 w-8 place-items-center rounded-full text-xs ring-4 ring-background {{ $tones[$tone] ?? $tones['primary'] }}">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    </span>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <h3 class="text-base font-semibold">{{ $title }}</h3>
        @if ($verified)
            <x-nt.badge variant="success" icon="fa-circle-check">Vérifié</x-nt.badge>
        @endif
    </div>
    @if ($time)
        <p class="mt-0.5 text-xs text-muted-foreground">{{ $time }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-2 text-sm text-foreground/85">{{ $slot }}</div>
    @endif
</li>
