{{-- Initials avatar; the tint is picked deterministically from the name. --}}
@props(['name', 'size' => 'md'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $tints = [
        'bg-primary/15 text-primary-strong',
        'bg-gold/25 text-gold-strong',
        'bg-earth/12 text-earth',
        'bg-info/12 text-info-strong',
        'bg-eco-b/20 text-eco-b-strong',
    ];
    $sizes = ['sm' => 'h-8 w-8 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-14 w-14 text-lg'];
@endphp

<span {{ $attributes->class(['inline-grid shrink-0 place-items-center rounded-full font-heading font-semibold', $tints[crc32($name) % count($tints)], $sizes[$size] ?? $sizes['md']]) }}
      role="img" aria-label="{{ $name }}">
    <span aria-hidden="true">{{ $initials }}</span>
</span>
