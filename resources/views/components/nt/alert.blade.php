{{-- Works both as <x-nt.alert type="…"> and through @component('components.nt.alert', [...]) with @slot('title'). --}}
@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $styles = [
        'success' => ['border-primary/30 bg-primary/8', 'fa-circle-check text-primary-strong'],
        'info' => ['border-info/30 bg-info/8', 'fa-circle-info text-info-strong'],
        'warning' => ['border-warning/40 bg-warning/10', 'fa-triangle-exclamation text-warning-strong'],
        'danger' => ['border-danger/30 bg-danger/8', 'fa-circle-exclamation text-danger-strong'],
        'gold' => ['border-gold/50 bg-gold/15', 'fa-award text-gold-strong'],
    ];
    [$box, $icon] = $styles[$type] ?? $styles['info'];
@endphp

<div x-data="{ shown: true }" x-show="shown" x-transition.opacity
     {{ $attributes->class(['flex items-start gap-3 rounded-lg border p-4 text-sm', $box]) }}
     role="{{ $type === 'danger' ? 'alert' : 'status' }}">
    <i class="fa-solid {{ $icon }} mt-0.5 text-base" aria-hidden="true"></i>
    <div class="flex-1 space-y-1">
        @if (filled((string) $title))
            <p class="font-semibold text-foreground">{{ $title }}</p>
        @endif
        <div class="text-foreground/85">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" x-on:click="shown = false" class="text-muted-foreground transition hover:text-foreground" aria-label="Fermer le message">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
