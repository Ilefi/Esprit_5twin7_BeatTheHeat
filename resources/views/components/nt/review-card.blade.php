{{-- One review with optional producer reply. <x-slot:actions> for account/admin buttons. --}}
@props(['review', 'showProduct' => false, 'showStatus' => false, 'interactive' => true])

<article {{ $attributes->class(['nt-card p-5 sm:p-6']) }} aria-labelledby="review-{{ $review->id }}-title">
    <header class="flex items-start gap-3">
        <x-nt.avatar :name="$review->user->name" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <p class="font-semibold">{{ $review->user->name }}</p>
                @if ($review->verified_purchase)
                    <x-nt.badge variant="success" icon="fa-bag-shopping">Achat vérifié</x-nt.badge>
                @endif
                @if ($showStatus)
                    <x-status-badge type="review" :value="$review->status" />
                @endif
            </div>
            <p class="text-xs text-muted-foreground">
                <time datetime="{{ $review->created_at->toIso8601String() }}" title="{{ $review->created_at->translatedFormat('d F Y à H:i') }}">{{ $review->created_at->diffForHumans() }}</time>
                @if ($showProduct)
                    · <a href="{{ route('front.products.show', $review->product->slug) }}#avis" class="nt-link">{{ $review->product->name }}</a>
                @endif
            </p>
        </div>
        <x-nt.rating-stars :rating="$review->rating" />
    </header>

    <h3 id="review-{{ $review->id }}-title" class="mt-4 text-base font-semibold">{{ $review->title }}</h3>
    <p class="mt-1.5 text-sm leading-relaxed text-foreground/85">{{ $review->body }}</p>

    <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-xs text-muted-foreground">
        <div class="flex gap-1"><dt>Qualité</dt><dd class="font-semibold text-foreground">{{ $review->quality_rating }}/5</dd></div>
        <div class="flex gap-1"><dt>Transparence</dt><dd class="font-semibold text-foreground">{{ $review->transparency_rating }}/5</dd></div>
        <div class="flex gap-1"><dt>Rapport qualité/prix</dt><dd class="font-semibold text-foreground">{{ $review->value_rating }}/5</dd></div>
    </dl>

    @if ($review->reply)
        <div class="mt-4 rounded-lg border-s-4 border-primary bg-primary/6 p-4">
            <p class="flex items-center gap-2 text-sm font-semibold text-primary-strong">
                <i class="fa-solid fa-reply" aria-hidden="true"></i> Réponse de {{ $review->reply->author }}
            </p>
            <p class="mt-1 text-sm text-foreground/85">{{ $review->reply->body }}</p>
        </div>
    @endif

    @if ($interactive || isset($actions))
        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
            @if ($interactive)
                <form method="POST" action="{{ route('front.reviews.helpful', $review->id) }}">
                    @csrf
                    <button type="submit" class="nt-btn nt-btn-ghost nt-btn-sm" aria-label="Marquer cet avis comme utile ({{ $review->helpful_count }} votes)">
                        <i class="fa-regular fa-thumbs-up" aria-hidden="true"></i> Utile ({{ $review->helpful_count }})
                    </button>
                </form>
            @endif
            {{ $actions ?? '' }}
        </footer>
    @endif
</article>
