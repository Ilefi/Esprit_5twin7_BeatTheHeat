{{-- Full reviews block (Module 4) — product page "Avis" tab. --}}
<div class="grid gap-8 lg:grid-cols-[20rem_minmax(0,1fr)]">
    <div class="space-y-4">
        @include('partials.front.reviews.summary', ['stats' => $reviewStats])
        <x-nt.button href="#ecrire-un-avis" variant="outline" class="w-full" icon="fa-pen-to-square">Écrire un avis</x-nt.button>
    </div>

    <div class="min-w-0 space-y-6">
        {{-- AI Feature 3 : Synthèse IA des Avis Consommateurs --}}
        @php $aiSummary = app(\App\Services\AiAnalysisService::class)->summarizeProductReviews($product); @endphp
        @if ($aiSummary)
            <div class="nt-card border-primary/30 bg-primary/5 p-5 relative overflow-hidden">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary text-primary-foreground text-sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-foreground">Synthèse IA des retours consommateurs</h3>
                            <p class="text-xs text-muted-foreground">{{ $aiSummary['headline'] }}</p>
                        </div>
                    </div>
                    <span class="nt-badge bg-primary/15 text-primary-strong text-xs font-bold">
                        <i class="fa-solid fa-thumbs-up me-1"></i> {{ $aiSummary['satisfaction_rate'] }}% satisfaction
                    </span>
                </div>

                <p class="text-sm text-foreground/90 leading-relaxed mb-4">
                    {{ $aiSummary['summary'] }}
                </p>

                <div class="grid gap-3 sm:grid-cols-2 text-xs border-t border-primary/15 pt-3">
                    <div class="space-y-1">
                        <p class="font-semibold text-primary flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> Points forts relevés par l'IA :
                        </p>
                        <ul class="list-disc list-inside text-muted-foreground space-y-0.5 ps-1">
                            @foreach ($aiSummary['pros'] as $pro)
                                <li>{{ $pro }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-gold-strong flex items-center gap-1">
                            <i class="fa-solid fa-circle-info"></i> Point de vigilance :
                        </p>
                        <p class="text-muted-foreground mt-1">{{ $aiSummary['cons'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        @include('partials.front.reviews.filters')

        <div>
            @each('partials.front.reviews.item', $reviews, 'review', 'partials.front.reviews.empty')
        </div>

        @includeWhen(auth()->check(), 'partials.front.reviews.form', ['product' => $product])
        @includeUnless(auth()->check(), 'partials.front.reviews.guest-cta')
    </div>
</div>
