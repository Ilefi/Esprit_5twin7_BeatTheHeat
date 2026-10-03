{{-- Generic label badge (icon + text). Official label logos are never reproduced. --}}
@props(['certification', 'full' => false])

@php
    $styles = [
        'bio' => ['fa-seedling', 'nt-badge nt-badge-primary'],
        'local' => ['fa-location-dot', 'nt-badge nt-badge-earth'],
        'fair' => ['fa-handshake', 'nt-badge nt-badge-gold'],
        'origin' => ['fa-map-location-dot', 'nt-badge nt-badge-info'],
        'no_pesticide' => ['fa-shield-halved', 'nt-badge nt-badge-success'],
        'reasoned' => ['fa-scale-balanced', 'nt-badge'],
    ];
    [$icon, $classes] = $styles[$certification->type] ?? ['fa-award', 'nt-badge'];
@endphp

<span {{ $attributes->class([$classes]) }} title="{{ $certification->name }}">
    <i class="fa-solid {{ $icon }} text-[0.75em]" aria-hidden="true"></i>
    {{ $full ? $certification->name : $certification->short_name }}
</span>
