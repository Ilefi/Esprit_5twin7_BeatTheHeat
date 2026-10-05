@props(['review', 'showProduct' => false, 'showStatus' => false, 'interactive' => true])

@php
    $isOwner = auth()->check() && auth()->id() === $review->user_id;
@endphp

<article {{ $attributes->class(['nt-card p-5 sm:p-6', 'border-warning/40 bg-warning/5' => $isOwner && $review->status === 'pending']) }} aria-labelledby="review-{{ $review->id }}-title">
    <header class="flex items-start gap-3">
        <x-nt.avatar :name="$review->user->name" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <p class="font-semibold">{{ $review->user->name }}</p>
                @if ($isOwner)
                    <span class="nt-badge bg-primary/15 text-primary-strong text-xs">Mon avis</span>
                @endif
                @if ($review->verified_purchase)
                    <x-nt.badge variant="success" icon="fa-bag-shopping">Achat vérifié</x-nt.badge>
                @endif
                @if ($showStatus || ($isOwner && $review->status === 'pending'))
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

    @if ($isOwner && $review->status === 'pending')
        <div class="mt-3 rounded-lg bg-warning/15 px-3 py-2 text-xs text-warning-strong flex items-center gap-2">
            <i class="fa-solid fa-clock"></i>
            <span>Cet avis n'a pas encore été examiné par un modérateur. Vous pouvez encore le modifier ou le supprimer.</span>
        </div>
    @endif

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

    @if ($interactive || isset($actions) || $isOwner)
        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
            @if ($interactive && $review->status === 'published')
                <form method="POST" action="{{ route('front.reviews.helpful', $review->id) }}">
                    @csrf
                    <button type="submit" class="nt-btn nt-btn-ghost nt-btn-sm" aria-label="Marquer cet avis comme utile ({{ $review->helpful_count }} votes)">
                        <i class="fa-regular fa-thumbs-up" aria-hidden="true"></i> Utile ({{ $review->helpful_count }})
                    </button>
                </form>
            @else
                <div></div>
            @endif

            @if (isset($actions))
                {{ $actions }}
            @elseif ($isOwner)
                <div class="flex items-center gap-2">
                    <x-nt.button variant="outline" size="sm" icon="fa-pen" x-data x-on:click="$dispatch('open-modal', 'edit-review-{{ $review->id }}')">
                        Modifier
                    </x-nt.button>
                    <x-nt.button variant="ghost" size="sm" icon="fa-trash" class="text-danger-strong hover:bg-danger/10" x-data x-on:click="$dispatch('open-modal', 'delete-review-{{ $review->id }}')">
                        Supprimer
                    </x-nt.button>
                </div>
            @endif
        </footer>
    @endif

    @if ($isOwner)
        <x-nt.modal :name="'edit-review-'.$review->id" title="Modifier mon avis" max-width="xl">
            <form method="POST" action="{{ route('account.reviews.update', $review->id) }}" class="space-y-5" id="edit-review-form-{{ $review->id }}">
                @csrf
                @method('PUT')
                <div>
                    <p class="nt-label">Note</p>
                    <x-nt.rating-stars input name="rating" :value="$review->rating" label="Note" size="md" />
                </div>
                <x-nt.form.input name="title" label="Titre" :value="$review->title" :id="'title-'.$review->id" required maxlength="120" />
                <x-nt.form.textarea name="body" label="Commentaire" :value="$review->body" :id="'body-'.$review->id" required :maxlength="1500" rows="5" />
            </form>
            <x-slot:footer>
                <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                <x-nt.button type="submit" form="edit-review-form-{{ $review->id }}" icon="fa-check">Enregistrer</x-nt.button>
            </x-slot:footer>
        </x-nt.modal>

        <x-nt.modal :name="'delete-review-'.$review->id" title="Supprimer cet avis ?" max-width="md">
            <p class="text-sm text-muted-foreground">L'avis « <strong class="text-foreground">{{ $review->title }}</strong> » sera définitivement supprimé.</p>
            <x-slot:footer>
                <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                <form method="POST" action="{{ route('account.reviews.destroy', $review->id) }}">
                    @csrf
                    @method('DELETE')
                    <x-nt.button type="submit" variant="danger" icon="fa-trash">Supprimer</x-nt.button>
                </form>
            </x-slot:footer>
        </x-nt.modal>
    @endif
</article>
