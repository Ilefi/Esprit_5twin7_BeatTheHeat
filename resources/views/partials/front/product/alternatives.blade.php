{{-- "Meilleures alternatives" (App\Services\ProductRecommender): same or substitute category, better eco-score. --}}
<section class="nt-section" aria-labelledby="alternatives-title">
    <div class="nt-container">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="nt-eyebrow"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> Recommandation durable</p>
                <h2 id="alternatives-title" class="mt-1 text-2xl font-bold">Meilleures alternatives</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Produits de la catégorie « {{ $product->category->name }} »@if ($product->category->substitutes->isNotEmpty()) ou de catégories équivalentes ({{ $product->category->substitutes->pluck('name')->implode(', ') }})@endif
                    avec un meilleur éco-score, classés selon l'impact, les avis, les labels et la proximité.
                </p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($alternatives as $alternative)
                <article class="nt-card flex flex-col overflow-hidden">
                    <div class="relative">
                        <x-nt.product-image :product="$alternative" class="aspect-[16/9]" />
                        <x-nt.eco-score :grade="$alternative->eco_score" size="sm" class="absolute left-3 top-3" />
                        <span class="nt-badge nt-badge-solid absolute right-3 top-3">{{ $alternative->recommendation['score'] }} / 100</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $alternative->category->name }}</p>
                        <h3 class="mt-1 font-semibold leading-snug">
                            <a href="{{ route('front.products.show', $alternative->slug) }}" class="hover:text-primary-strong">{{ $alternative->name }}</a>
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $alternative->producer->name }} · {{ $alternative->region }}</p>

                        <ul class="mt-4 space-y-1.5 text-sm">
                            @foreach ($alternative->recommendation['reasons'] as $reason)
                                <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check mt-0.5 text-primary-strong" aria-hidden="true"></i>{{ $reason }}</li>
                            @endforeach
                        </ul>

                        <div class="mt-auto flex flex-wrap gap-2 pt-5">
                            <x-nt.button :href="route('front.products.show', $alternative->slug)" size="sm" icon="fa-eye">Voir</x-nt.button>
                            @if ($product->impact)
                                <x-nt.button :href="route('front.impact.compare', ['produits' => [$product->id, $alternative->id]])" variant="outline" size="sm" icon="fa-scale-unbalanced">Comparer</x-nt.button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
