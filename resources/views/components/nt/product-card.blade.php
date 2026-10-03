@props(['product', 'layout' => 'grid'])

<article {{ $attributes->class(['nt-card nt-card-hover group relative flex overflow-hidden', 'flex-col' => $layout === 'grid', 'flex-col sm:flex-row' => $layout === 'list']) }}>
    <div @class(['relative shrink-0', 'aspect-[4/3]' => $layout === 'grid', 'aspect-[4/3] sm:aspect-auto sm:w-56' => $layout === 'list'])>
        <x-nt.product-image :product="$product" class="h-full w-full transition duration-300 group-hover:scale-[1.03]" />
        <x-nt.eco-score :grade="$product->eco_score" size="sm" class="absolute left-3 top-3" />
        @if ($product->batch_code)
            <span class="nt-badge absolute right-3 top-3 bg-surface/90 text-foreground shadow-sm backdrop-blur">
                <i class="fa-solid fa-qrcode text-[0.7em]" aria-hidden="true"></i> Tracé
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $product->category->name }}</p>
        <h3 class="mt-1 text-lg font-semibold leading-snug">
            <a href="{{ route('front.products.show', $product->slug) }}" class="after:absolute after:inset-0 hover:text-primary-strong focus-visible:outline-none">
                {{ $product->name }}
            </a>
        </h3>
        <p class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
            <i class="fa-solid fa-location-dot text-xs" aria-hidden="true"></i>
            {{ $product->producer->name }} · {{ $product->region }}
        </p>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($product->certifications as $certification)
                <x-nt.cert-badge :certification="$certification" />
            @endforeach
        </div>

        @if ($layout === 'list')
            <p class="mt-3 line-clamp-2 text-sm text-muted-foreground">{{ $product->description }}</p>
        @endif

        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <span class="flex items-center gap-1.5 text-sm">
                <x-nt.rating-stars :rating="$product->rating_avg" size="xs" />
                <span class="text-muted-foreground">({{ $product->reviews_count }})</span>
            </span>
            <span class="font-heading font-semibold">{{ number_format($product->price, 2, ',', ' ') }} DT</span>
        </div>
    </div>
</article>
