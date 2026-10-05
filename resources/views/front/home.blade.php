@extends('layouts.front')

@section('title', 'De la ferme à l\'assiette')
@section('meta_description', 'NutriTrace retrace le parcours de vos aliments, vérifie les labels, mesure leur empreinte et vous permet de signaler le greenwashing.')

@push('scripts')
    <script type="application/ld+json">
            {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'NutriTrace',
        'slogan' => 'De la ferme à l\'assiette, en toute transparence',
        'url' => url('/'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
@endpush

@section('hero')
    {{-- 1. Hero --}}
    <section class="nt-gradient-hero relative overflow-hidden">
        <div class="nt-pattern" aria-hidden="true"></div>
        <div class="nt-container relative grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-24">
            <div class="nt-reveal">
                <p class="nt-eyebrow mb-4"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Traçabilité alimentaire
                    transparente</p>
                <h1 class="text-4xl font-extrabold leading-[1.1] sm:text-5xl lg:text-6xl">
                    De la ferme à l'assiette, <span class="text-primary">en toute transparence</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-muted-foreground">
                    Origine, étapes de transformation, labels vérifiés et empreinte environnementale : NutriTrace révèle
                    l'histoire réelle de vos aliments — et vous donne les moyens de dénoncer le greenwashing.
                </p>

                <form action="{{ route('front.traceability.index') }}" method="GET" role="search"
                    class="mt-8 flex max-w-xl flex-col gap-3 rounded-xl border bg-surface p-2 shadow-lg sm:flex-row">
                    <label for="hero-code" class="sr-only">Numéro de lot</label>
                    <div class="relative flex-1">
                        <i class="fa-solid fa-barcode pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"></i>
                        <input id="hero-code" name="code" type="search" placeholder="Saisissez un numéro de lot"
                            autocomplete="off"
                            class="h-12 w-full rounded-lg bg-transparent ps-11 pe-3 text-base focus:outline-none"
                            aria-describedby="hero-code-hint">
                    </div>
                    <button type="submit" class="nt-btn nt-btn-primary nt-btn-lg"><i class="fa-solid fa-magnifying-glass"
                            aria-hidden="true"></i> Tracer</button>
                </form>
                @if($exampleBatch)
                    <p id="hero-code-hint" class="mt-3 text-sm text-muted-foreground">
                        Essayez : <a href="{{ route('front.traceability.batch', $exampleBatch->code) }}"
                            class="nt-link font-mono">{{ $exampleBatch->code }}</a>
                    </p>
                @else
                    <p id="hero-code-hint" class="mt-3 text-sm text-muted-foreground">
                        Saisissez un numéro de lot pour tracer un produit.
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-nt.button :href="route('front.products.index')" size="lg" icon-right="fa-arrow-right">Explorer les
                        produits</x-nt.button>
                    <x-nt.button :href="route('front.traceability.index')" variant="outline" size="lg"
                        icon="fa-qrcode">Tracer un produit</x-nt.button>
                </div>
            </div>

            <div class="nt-reveal [animation-delay:150ms]">
                @include('partials.front.journey')
            </div>
        </div>
    </section>
@endsection

@section('content')
    {{-- 2. Trust band --}}
    <section aria-label="NutriTrace en chiffres" class="border-y bg-surface">
        <dl class="nt-container grid grid-cols-2 gap-6 py-10 lg:grid-cols-4">
            @foreach ($counters as $counter)
                <div class="nt-reveal flex items-center gap-4" style="animation-delay: {{ $loop->index * 80 }}ms">
                    <span
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-primary/10 text-lg text-primary-strong">
                        <i class="fa-solid {{ $counter['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <div>
                        <dd class="font-heading text-2xl font-bold sm:text-3xl" x-data="ntCounter({{ $counter['value'] }})">
                            <span x-text="display">{{ number_format($counter['value'], 0, ',', ' ') }}</span>
                        </dd>
                        <dt class="text-sm text-muted-foreground">{{ $counter['label'] }}</dt>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- 3. How it works --}}
    <section class="nt-section" aria-labelledby="how-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Comment ça marche" title="Quatre maillons, une seule chaîne de confiance"
                subtitle="Chaque acteur enregistre son étape et ses justificatifs. Vous voyez le résultat en un scan."
                id="how-title" />

            <ol class="relative grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <span
                    class="absolute left-[12%] right-[12%] top-10 hidden h-0.5 border-t-2 border-dashed border-primary/30 lg:block"
                    aria-hidden="true"></span>
                @foreach ([
                        ['fa-tractor', 'Producteur', 'Déclare la récolte, la parcelle et ses certificats.', 'primary'],
                        ['fa-industry', 'Transformateur', 'Enregistre la transformation, les analyses et le conditionnement.', 'earth'],
                        ['fa-truck', 'Distributeur', 'Trace le transport, le stockage et la mise en rayon.', 'gold'],
                        ['fa-utensils', 'Consommateur', 'Scanne, compare, note et signale les incohérences.', 'primary'],
                    ] as [$icon, $title, $text, $tone])
                    <li class="nt-reveal relative text-center" style="animation-delay: {{ $loop->index * 100 }}ms">
                        <span @class([
                            'relative z-10 mx-auto grid h-20 w-20 place-items-center rounded-2xl text-2xl shadow-md ring-8 ring-background',
                            'bg-primary text-primary-foreground' => $tone === 'primary',
                            'bg-earth text-earth-foreground' => $tone === 'earth',
                            'bg-gold text-gold-foreground' => $tone === 'gold',
                        ])>
                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                        </span>
                        <span
                            class="mt-4 block font-heading text-xs font-semibold uppercase tracking-wider text-muted-foreground">Étape
                            {{ $loop->iteration }}</span>
                        <h3 class="mt-1 text-xl font-semibold">{{ $title }}</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-muted-foreground">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 4. Four pillars (one per module) --}}
    <section class="nt-section bg-surface" aria-labelledby="pillars-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Nos quatre piliers" title="Tout ce qu'il faut savoir avant de remplir son panier"
                id="pillars-title" />

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                        ['fa-basket-shopping', 'Produits & Certifications', 'Fiches produits complètes et labels vérifiés par notre équipe, document à l\'appui.', 'front.products.index', 'Voir le catalogue', 'bg-primary/12 text-primary-strong'],
                        ['fa-route', 'Chaîne de traçabilité', 'Chaque lot raconte son voyage : acteurs, lieux, dates et preuves à chaque étape.', 'front.traceability.index', 'Tracer un lot', 'bg-earth/12 text-earth'],
                        ['fa-leaf', 'Empreinte environnementale', 'CO₂e, eau, distance, emballage : un éco-score de A à E, calcul transparent.', 'front.impact.index', 'Comprendre l\'éco-score', 'bg-eco-b/20 text-eco-b-strong'],
                        ['fa-bullhorn', 'Signalements & Avis', 'Notez vos produits, signalez une allégation douteuse et suivez la décision.', 'front.observatory', 'Voir l\'observatoire', 'bg-danger/10 text-danger-strong'],
                    ] as [$icon, $title, $text, $route, $cta, $tint])
                    <article class="nt-card nt-card-hover nt-reveal relative flex flex-col p-6"
                        style="animation-delay: {{ $loop->index * 80 }}ms">
                        <span class="grid h-14 w-14 place-items-center rounded-xl text-2xl {{ $tint }}">
                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                        </span>
                        <p class="mt-5 font-heading text-xs font-semibold uppercase tracking-wider text-muted-foreground">Module
                            {{ $loop->iteration }}</p>
                        <h3 class="mt-1 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 flex-1 text-sm text-muted-foreground">{{ $text }}</p>
                        <a href="{{ route($route) }}"
                            class="nt-link mt-5 inline-flex items-center gap-2 text-sm after:absolute after:inset-0">
                            {{ $cta }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Eco-score explainer --}}
    <section class="nt-section" aria-labelledby="eco-title">
        <div class="nt-container grid items-center gap-12 lg:grid-cols-[1fr_1.2fr]">
            <div class="nt-reveal">
                <p class="nt-eyebrow mb-3">L'éco-score NutriTrace</p>
                <h2 id="eco-title" class="text-3xl font-bold sm:text-4xl">Une lettre, cinq indicateurs, zéro mystère</h2>
                <p class="mt-4 text-lg text-muted-foreground">Émissions de CO₂e, consommation d'eau, kilomètres parcourus,
                    type d'emballage et saisonnalité sont combinés en un score sur 100, puis traduits en une note de A à E.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-6">
                    <x-nt.eco-score grade="A" size="lg" show-label />
                    <x-nt.button :href="route('front.impact.index')" variant="outline" icon-right="fa-arrow-right">Voir la
                        méthodologie</x-nt.button>
                </div>
            </div>

            <ul class="nt-reveal space-y-3">
                @foreach ($grades as $letter => $grade)
                    <li class="nt-card flex items-center gap-4 p-4">
                        <span @class([
                            'grid h-12 w-12 shrink-0 place-items-center rounded-lg font-heading text-xl font-bold',
                            'bg-eco-a text-primary-foreground' => $letter === 'A',
                            'bg-eco-b text-foreground' => $letter === 'B',
                            'bg-eco-c text-foreground' => $letter === 'C',
                            'bg-eco-d text-foreground' => $letter === 'D',
                            'bg-eco-e text-danger-foreground' => $letter === 'E',
                        ])>{{ $letter }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-baseline justify-between gap-x-3 font-semibold">{{ $grade['label'] }}
                                <span class="text-xs font-normal text-muted-foreground">{{ $grade['range'] }}</span></p>
                            <p class="text-sm text-muted-foreground">{{ $grade['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- 6. Featured products --}}
    <section class="nt-section bg-surface" aria-labelledby="featured-title">
        <div class="nt-container">
            <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <x-nt.section-header eyebrow="Sélection du moment" title="Des produits tracés de bout en bout" align="left"
                    class="mb-0! lg:mb-0!" id="featured-title" />
                <x-nt.button :href="route('front.products.index')" variant="outline" icon-right="fa-arrow-right">Tout le
                    catalogue</x-nt.button>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-nt.product-card :product="$product" class="nt-reveal" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. Anti-greenwashing --}}
    <section class="relative overflow-hidden bg-earth text-earth-muted" aria-labelledby="greenwashing-title">
        <div class="nt-pattern nt-pattern-light" aria-hidden="true"></div>
        <div class="nt-container nt-section relative grid items-center gap-12 lg:grid-cols-2">
            <div class="nt-reveal">
                <p class="font-heading text-sm font-semibold uppercase tracking-wider text-gold">Stop au greenwashing</p>
                <h2 id="greenwashing-title" class="mt-3 text-3xl font-bold text-earth-foreground sm:text-4xl">«
                    Éco-responsable », « naturel », « zéro impact »… vraiment ?</h2>
                <p class="mt-4 text-lg">Les allégations vertes sont partout, les preuves beaucoup moins. Sur NutriTrace,
                    chaque promesse est confrontée aux données de traçabilité et d'empreinte — et chacun peut signaler un
                    écart.</p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Comparaison automatique allégation / données', 'Examen par un modérateur sous 7 jours', 'Décision publique dans l\'Observatoire'] as $point)
                        <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-gold"
                                aria-hidden="true"></i><span class="text-earth-foreground">{{ $point }}</span></li>
                    @endforeach
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-nt.button :href="route('front.reports.create', ['type' => 'greenwashing'])" variant="gold" size="lg"
                        icon="fa-flag">Signaler une allégation douteuse</x-nt.button>
                    <a href="{{ route('front.observatory') }}"
                        class="nt-btn nt-btn-lg border-earth-foreground/30 text-earth-foreground hover:bg-earth-foreground/10">L'Observatoire</a>
                </div>
            </div>

            <div class="nt-reveal grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border-2 border-dashed border-gold/60 bg-earth-foreground/5 p-6">
                    <p class="nt-badge nt-badge-gold"><i class="fa-solid fa-bullhorn text-[0.7em]" aria-hidden="true"></i>
                        L'allégation</p>
                    <p class="mt-4 font-heading text-xl font-semibold text-earth-foreground">« Emballage 100 %
                        éco-responsable »</p>
                    <p class="mt-2 text-sm">Mention imprimée en vert sur une barquette de fromage frais.</p>
                </div>
                <div class="rounded-xl bg-surface p-6 text-foreground shadow-lg">
                    <p class="nt-badge nt-badge-danger"><i class="fa-solid fa-magnifying-glass text-[0.7em]"
                            aria-hidden="true"></i> La réalité</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex justify-between gap-3"><span
                                class="text-muted-foreground">Emballage</span><strong>Plastique non recyclable</strong></li>
                        <li class="flex justify-between gap-3"><span
                                class="text-muted-foreground">Émissions</span><strong>8,5 kg CO₂e/kg</strong></li>
                        <li class="flex items-center justify-between gap-3"><span
                                class="text-muted-foreground">Éco-score</span><x-nt.eco-score grade="E" size="sm" /></li>
                    </ul>
                    <p class="mt-4 rounded-lg bg-danger/8 p-3 text-sm font-medium text-danger-strong"><i
                            class="fa-solid fa-gavel me-1" aria-hidden="true"></i> Signalement fondé — allégation retirée
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. Labels strip --}}
    <section class="nt-section" aria-labelledby="labels-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Labels & certifications" title="Ce que chaque label garantit vraiment"
                subtitle="Des badges génériques, des critères clairs, des certificats vérifiés." id="labels-title" />
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($certifications as $certification)
                    <a href="{{ route('front.certifications.show', $certification->slug) }}"
                        class="nt-card nt-card-hover nt-reveal flex items-start gap-4 p-5">
                        <x-nt.cert-badge :certification="$certification" class="mt-0.5 shrink-0" />
                        <span
                            class="text-sm text-muted-foreground">{{ \Illuminate\Support\Str::before($certification->description, ',') }}.</span>
                    </a>
                @endforeach
            </div>
            <p class="mt-8 text-center">
                <a href="{{ route('front.certifications.index') }}" class="nt-link">Comment repérer un faux label ? <i
                        class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
            </p>
        </div>
    </section>

    {{-- 9. Testimonials --}}
    <section class="nt-section bg-surface" aria-labelledby="testimonials-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Ils nous font confiance" title="Consommateurs et producteurs en parlent"
                id="testimonials-title" />
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($testimonials as $testimonial)
                    <figure class="nt-card nt-reveal flex flex-col p-6">
                        <i class="fa-solid fa-quote-left text-2xl text-primary/30" aria-hidden="true"></i>
                        <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-foreground/90">« {{ $testimonial->quote }} »
                        </blockquote>
                        <x-nt.rating-stars :rating="$testimonial->rating" class="mt-4" />
                        <figcaption class="mt-4 flex items-center gap-3 border-t pt-4">
                            <x-nt.avatar :name="$testimonial->name" size="sm" />
                            <div>
                                <p class="text-sm font-semibold">{{ $testimonial->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $testimonial->role }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endsection

{{-- Producer CTA + FAQ, then the default newsletter block through @parent. --}}
@section('pre_footer')
    {{-- 10. Producer CTA --}}
    <section class="nt-section" aria-labelledby="producer-title">
        <div class="nt-container">
            <div class="nt-card relative grid items-center gap-8 overflow-hidden p-8 sm:p-12 lg:grid-cols-[1.4fr_1fr]">
                <div class="nt-pattern" aria-hidden="true"></div>
                <div class="relative">
                    <p class="nt-eyebrow mb-3"><i class="fa-solid fa-tractor" aria-hidden="true"></i> Producteurs & artisans
                    </p>
                    <h2 id="producer-title" class="text-3xl font-bold">Vous êtes producteur ? Rejoignez NutriTrace</h2>
                    <p class="mt-3 text-lg text-muted-foreground">Valorisez vos pratiques, prouvez vos labels et racontez
                        l'histoire de vos lots. L'inscription et l'accompagnement sont gratuits pendant la phase pilote.</p>
                </div>
                <div class="relative flex flex-wrap gap-3 lg:justify-end">
                    <x-nt.button :href="route('register')" size="lg" icon="fa-user-plus">Créer mon compte</x-nt.button>
                    <x-nt.button :href="route('front.contact')" variant="outline" size="lg">Nous contacter</x-nt.button>
                </div>
            </div>
        </div>
    </section>

    {{-- 11. FAQ --}}
    <section class="nt-section pt-0" aria-labelledby="faq-title">
        <div class="nt-container grid gap-10 lg:grid-cols-[1fr_1.6fr]">
            <div>
                <x-nt.section-header eyebrow="Questions fréquentes" title="Vous vous demandez…" align="left"
                    id="faq-title" />
                <x-nt.button :href="route('front.faq')" variant="outline" icon-right="fa-arrow-right">Toutes les
                    questions</x-nt.button>
            </div>
            <x-nt.accordion open="faq-0">
                @foreach ($faqs->take(4) as $faq)
                    <x-nt.accordion-item :id="'faq-' . $loop->index"
                        :title="$faq->question">{{ $faq->answer }}</x-nt.accordion-item>
                @endforeach
            </x-nt.accordion>
        </div>
    </section>

    {{-- 12. Newsletter (layout default) --}}
    @parent
@endsection