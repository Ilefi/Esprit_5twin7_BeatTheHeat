@props(['variant' => 'muted', 'icon' => null])

@php
    $variants = [
        'muted' => 'nt-badge',
        'primary' => 'nt-badge nt-badge-primary',
        'success' => 'nt-badge nt-badge-success',
        'gold' => 'nt-badge nt-badge-gold',
        'earth' => 'nt-badge nt-badge-earth',
        'danger' => 'nt-badge nt-badge-danger',
        'warning' => 'nt-badge nt-badge-warning',
        'info' => 'nt-badge nt-badge-info',
        'solid' => 'nt-badge nt-badge-solid',
    ];
@endphp

<span {{ $attributes->class([$variants[$variant] ?? $variants['muted']]) }}>
    @if ($icon)
        <i class="fa-solid {{ $icon }} text-[0.7em]" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</span>
