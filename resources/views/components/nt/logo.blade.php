{{-- NutriTrace logo: leaf + dotted trace path. variant="light" for dark backgrounds, :wordmark="false" for the mark only. --}}
@props(['variant' => 'default', 'wordmark' => true])

@php
    $wordClasses = [
        'default' => ['text-foreground', 'text-primary'],
        'light' => ['text-earth-foreground', 'text-gold'],
    ][$variant] ?? ['text-foreground', 'text-primary'];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 40 40" class="h-9 w-9 shrink-0" aria-hidden="true" focusable="false">
        <path class="{{ $variant === 'light' ? 'fill-primary-light' : 'fill-primary' }}"
              d="M22 3c9 1.5 15 9 14.2 19.4C35.5 31.6 28 37.4 19.6 36.6 11 35.8 5 28.3 6 19.8 7 10.6 13.6 2 22 3Z"/>
        <path class="stroke-primary-foreground" fill="none" stroke-width="2.2" stroke-linecap="round"
              d="M14 30.5C18.5 24 23.5 17.5 30 10.5"/>
        <path class="stroke-primary-foreground/80" fill="none" stroke-width="1.8" stroke-linecap="round"
              d="M20.5 22.5 19.5 15.5M24.5 17.5l5 1"/>
        <path class="stroke-gold" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-dasharray="0.1 4.4"
              d="M2.5 38.5C6 35 9.5 33.5 14 30.5"/>
        <circle class="fill-gold" cx="2.6" cy="38.2" r="1.9"/>
    </svg>
    @if ($wordmark)
        <span class="font-heading text-xl font-bold tracking-tight">
            <span class="{{ $wordClasses[0] }}">Nutri</span><span class="{{ $wordClasses[1] }}">Trace</span>
        </span>
    @endif
</span>
