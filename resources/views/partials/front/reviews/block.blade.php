{{-- Full reviews block (Module 4) — product page "Avis" tab. --}}
<div class="grid gap-8 lg:grid-cols-[20rem_minmax(0,1fr)]">
    <div class="space-y-4">
        @include('partials.front.reviews.summary', ['stats' => $reviewStats])
        <x-nt.button href="#ecrire-un-avis" variant="outline" class="w-full" icon="fa-pen-to-square">Écrire un avis</x-nt.button>
    </div>

    <div class="min-w-0 space-y-6">
        @include('partials.front.reviews.filters')

        <div>
            @each('partials.front.reviews.item', $reviews, 'review', 'partials.front.reviews.empty')
        </div>

        @includeWhen(auth()->check(), 'partials.front.reviews.form', ['product' => $product])
        @includeUnless(auth()->check(), 'partials.front.reviews.guest-cta')
    </div>
</div>
