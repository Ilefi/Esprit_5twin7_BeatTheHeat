@extends('layouts.front')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit($product->description, 155))

@section('content')
    <section class="nt-gradient-hero relative overflow-hidden border-b">
        <div class="nt-pattern" aria-hidden="true"></div>
        <div class="nt-container relative py-8 lg:py-12">
            @include('partials.shared.breadcrumb', ['items' => [
                ['label' => 'Accueil', 'url' => route('front.home')],
                ['label' => 'Produits', 'url' => route('front.products.index')],
                ['label' => $product->category->name, 'url' => route('front.products.index', ['categorie' => $product->category->slug])],
                ['label' => $product->name],
            ], 'class' => 'mb-6'])

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-12">
                <div class="relative self-start">
                    <x-nt.product-image :product="$product" size="lg" class="aspect-[4/3] rounded-xl border shadow-md" />
                    @if ($batch)
                        <a href="{{ route('front.traceability.batch', $batch->code) }}" class="nt-card absolute bottom-4 left-4 flex items-center gap-3 px-4 py-3 shadow-lg transition hover:-translate-y-0.5">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary text-primary-foreground"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span>
                            <span><span class="block text-xs text-muted-foreground">Lot tracé</span><span class="font-mono text-sm font-semibold">{{ $batch->code }}</span></span>
                        </a>
                    @endif
                </div>

                <div>
                    <p class="nt-eyebrow">{{ $product->category->name }} · {{ $product->region }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ $product->name }}</h1>
                    <p class="mt-2 text-muted-foreground">
                        Produit par <a href="{{ route('front.actors.show', $product->producer->slug) }}" class="nt-link">{{ $product->producer->name }}</a>
                        @if ($product->processor)
                            · transformé par <a href="{{ route('front.actors.show', $product->processor->slug) }}" class="nt-link">{{ $product->processor->name }}</a>
                        @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="#avis" class="flex items-center gap-2 text-sm">
                            <x-nt.rating-stars :rating="$product->rating_avg" />
                            <span class="font-semibold">{{ number_format($product->rating_avg, 1, ',', ' ') }}</span>
                            <span class="text-muted-foreground underline">({{ $product->reviews_count }} avis)</span>
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap items-end justify-between gap-6 rounded-xl border bg-surface p-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Éco-score</p>
                            <x-nt.eco-score :grade="$product->eco_score" size="lg" show-label class="mt-2" />
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-muted-foreground">{{ $product->format }}</p>
                            <p class="font-heading text-3xl font-bold">{{ number_format($product->price, 2, ',', ' ') }} <span class="text-lg">DT</span></p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <p class="mb-2 text-sm font-semibold">Certifications vérifiées</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($product->certifications as $certification)
                                <a href="{{ route('front.certifications.show', $certification->slug) }}"><x-nt.cert-badge :certification="$certification" full class="px-3 py-1 text-sm" /></a>
                            @empty
                                <span class="text-sm text-muted-foreground">Aucune certification déclarée.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($batch)
                            <x-nt.button :href="route('front.traceability.batch', $batch->code)" icon="fa-route">Voir le parcours</x-nt.button>
                        @endif
                        <x-nt.button :href="route('front.impact.compare', ['produits' => array_filter([$product->id, $similarProducts->first()?->id])])" variant="outline" icon="fa-scale-unbalanced">Comparer l'empreinte</x-nt.button>
                        <x-nt.button :href="route('front.reports.create', ['cible' => 'product', 'id' => $product->id])" variant="ghost" icon="fa-flag" class="text-danger-strong">Signaler ce produit</x-nt.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="nt-container py-10" id="details">
        <x-nt.tabs :tabs="[
            'description' => ['label' => 'Description', 'icon' => 'fa-align-left'],
            'certifications' => ['label' => 'Certifications', 'icon' => 'fa-award', 'count' => $product->certifications->count()],
            'tracabilite' => ['label' => 'Traçabilité', 'icon' => 'fa-route'],
            'empreinte' => ['label' => 'Empreinte', 'icon' => 'fa-leaf'],
            'avis' => ['label' => 'Avis', 'icon' => 'fa-star', 'count' => $reviewStats->count],
        ]" :active="$errors->review->any() || request()->hasAny(['note', 'tri_avis', 'verifie']) ? 'avis' : 'description'">

            <x-nt.tab-panel name="description">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">À propos de ce produit</h2>
                        <p class="text-foreground/85">{{ $product->description }}</p>
                        <h3 class="pt-2 text-base font-semibold">Composition</h3>
                        <p class="text-foreground/85">{{ $product->composition }}</p>
                    </div>
                    <dl class="nt-card divide-y text-sm">
                        @foreach (['Catégorie' => $product->category->name, 'Origine' => $product->region, 'Format' => $product->format, 'Producteur' => $product->producer->name, 'Transformateur' => $product->processor?->name ?? '—'] as $label => $value)
                            <div class="flex justify-between gap-3 px-5 py-3"><dt class="text-muted-foreground">{{ $label }}</dt><dd class="text-right font-medium">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </x-nt.tab-panel>

            <x-nt.tab-panel name="certifications">
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($product->certifications as $certification)
                        <x-nt.card>
                            <div class="flex items-start justify-between gap-3">
                                <x-nt.cert-badge :certification="$certification" full />
                                <x-nt.badge variant="success" icon="fa-circle-check">Certificat vérifié</x-nt.badge>
                            </div>
                            <p class="mt-3 text-sm text-foreground/85">{{ $certification->description }}</p>
                            <p class="mt-2 text-xs text-muted-foreground">Délivré par {{ $certification->issuer }}</p>
                            <x-slot:footer>
                                <a href="{{ route('front.certifications.show', $certification->slug) }}" class="nt-link text-sm">Ce que garantit ce label</a>
                            </x-slot:footer>
                        </x-nt.card>
                    @empty
                        <x-nt.empty-state icon="fa-award" title="Aucune certification" description="Ce produit n'affiche aucun label." class="md:col-span-2" />
                    @endforelse
                </div>
            </x-nt.tab-panel>

            <x-nt.tab-panel name="tracabilite">
                @if ($batch)
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                        <x-nt.timeline>
                            @foreach ($batch->steps as $step)
                                <x-nt.timeline-item :icon="['production' => 'fa-tractor', 'processing' => 'fa-industry', 'distribution' => 'fa-truck', 'consumer' => 'fa-utensils'][$step->stage]"
                                                    :title="$step->title" :time="$step->date->translatedFormat('d M Y').' · '.$step->location" :verified="$step->verified"
                                                    :tone="$step->stage === 'consumer' ? 'gold' : 'primary'">
                                    {{ $step->action }}
                                </x-nt.timeline-item>
                            @endforeach
                        </x-nt.timeline>
                        <div class="nt-card h-fit p-5">
                            <p class="text-sm text-muted-foreground">Lot</p>
                            <p class="font-mono text-lg font-semibold">{{ $batch->code }}</p>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div><dt class="text-muted-foreground">Étapes</dt><dd class="font-semibold">{{ $batch->steps->count() }}</dd></div>
                                <div><dt class="text-muted-foreground">Distance</dt><dd class="font-semibold">{{ $batch->total_km }} km</dd></div>
                                <div><dt class="text-muted-foreground">Acteurs</dt><dd class="font-semibold">{{ $batch->actors_count }}</dd></div>
                                <div><dt class="text-muted-foreground">Statut</dt><dd><x-status-badge type="batch" :value="$batch->status" /></dd></div>
                            </dl>
                            <x-nt.button :href="route('front.traceability.batch', $batch->code)" class="mt-5 w-full" icon="fa-route">Parcours complet</x-nt.button>
                        </div>
                    </div>
                @else
                    <x-nt.empty-state icon="fa-route" title="Aucun lot tracé pour l'instant" description="Le producteur n'a pas encore publié de lot pour ce produit." />
                @endif
            </x-nt.tab-panel>

            <x-nt.tab-panel name="empreinte">
                @include('partials.front.impact-summary', ['product' => $product])
            </x-nt.tab-panel>

            <x-nt.tab-panel name="avis">
                @include('partials.front.reviews.block')
            </x-nt.tab-panel>
        </x-nt.tabs>
    </section>

    @if ($similarProducts->isNotEmpty())
        <section class="nt-section bg-surface" aria-labelledby="similar-title">
            <div class="nt-container">
                <h2 id="similar-title" class="mb-8 text-2xl font-bold">Produits similaires</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similarProducts as $similar)
                        <x-nt.product-card :product="$similar" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
