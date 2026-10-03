{{-- Renders an <a> when href is given, otherwise a <button>. --}}
@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'nt-btn-primary',
        'secondary' => 'nt-btn-secondary',
        'outline' => 'nt-btn-outline',
        'ghost' => 'nt-btn-ghost',
        'gold' => 'nt-btn-gold',
        'danger' => 'nt-btn-danger',
    ];
    $sizes = ['sm' => 'nt-btn-sm', 'lg' => 'nt-btn-lg', 'icon' => 'nt-btn-icon'];
    $classes = ['nt-btn', $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? ''];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
@endif
    @if ($icon)
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
    @if ($iconRight)
        <i class="fa-solid {{ $iconRight }}" aria-hidden="true"></i>
    @endif
@if ($href)
    </a>
@else
    </button>
@endif
