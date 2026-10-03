{{-- Product visual: local image if set, otherwise a tinted gradient with the category icon. No remote images. --}}
@props(['product', 'size' => 'md'])

@php
    $tints = [
        'huiles-condiments' => 'from-gold/35 via-gold/10 to-primary/20 text-gold-strong',
        'fruits' => 'from-eco-d/25 via-gold/15 to-primary/15 text-eco-d-strong',
        'fruits-secs' => 'from-earth/25 via-gold/15 to-earth/10 text-earth',
        'epicerie-cereales' => 'from-gold/30 via-muted to-earth/15 text-earth',
        'produits-ruche' => 'from-gold/45 via-gold/15 to-eco-d/15 text-gold-strong',
        'produits-laitiers' => 'from-info/15 via-surface to-primary/15 text-info-strong',
    ];
    $icons = ['sm' => 'text-xl', 'md' => 'text-5xl', 'lg' => 'text-7xl'];
@endphp

<div {{ $attributes->class(['relative grid place-items-center overflow-hidden bg-linear-to-br', $tints[$product->category->slug] ?? 'from-primary/20 to-gold/20 text-primary-strong']) }}>
    @if ($product->image)
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <svg class="absolute inset-0 h-full w-full opacity-40" viewBox="0 0 200 120" preserveAspectRatio="none" aria-hidden="true">
            <path class="fill-surface/50" d="M0 90 Q50 70 100 85 T200 75 V120 H0Z"/>
            <path class="fill-surface/40" d="M0 105 Q60 90 120 102 T200 95 V120 H0Z"/>
        </svg>
        <i class="fa-solid {{ $product->category->icon }} relative drop-shadow-sm {{ $icons[$size] ?? $icons['md'] }}" aria-hidden="true"></i>
        <span class="sr-only">Visuel indicatif : {{ $product->category->name }}</span>
    @endif
</div>
