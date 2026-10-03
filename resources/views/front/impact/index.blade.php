@extends('layouts.front')

@section('title', 'Empreinte environnementale')
@section('meta_description', 'Méthodologie de l\'éco-score NutriTrace : CO₂e, eau, distance, emballage et saisonnalité.')

@section('hero')
    <x-nt.page-hero eyebrow="Module 3 · Empreinte environnementale" title="Comment nous calculons l'éco-score"
                    subtitle="Cinq indicateurs mesurés à chaque étape du lot, combinés en un score sur 100 puis en une note de A à E."
                    :breadcrumb="[['label' => 'Empreinte']]">
        <x-nt.button :href="route('front.impact.compare')" icon="fa-scale-unbalanced">Comparer deux produits</x-nt.button>
    </x-nt.page-hero>
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container">
            <x-nt.section-header eyebrow="L'échelle" title="De A à E" />
            <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($grades as $letter => $grade)
                    <li class="nt-card overflow-hidden">
                        <div @class([
                            'grid h-24 place-items-center font-heading text-5xl font-extrabold',
                            'bg-eco-a text-primary-foreground' => $letter === 'A',
                            'bg-eco-b text-foreground' => $letter === 'B',
                            'bg-eco-c text-foreground' => $letter === 'C',
                            'bg-eco-d text-foreground' => $letter === 'D',
                            'bg-eco-e text-danger-foreground' => $letter === 'E',
                        ])>{{ $letter }}</div>
                        <div class="p-4">
                            <p class="font-semibold">{{ $grade['label'] }}</p>
                            <p class="text-xs font-medium text-muted-foreground">{{ $grade['range'] }}</p>
                            <p class="mt-2 text-sm text-foreground/85">{{ $grade['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="nt-section bg-surface" aria-labelledby="indicators-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Les indicateurs" title="Cinq mesures, un score" id="indicators-title" />
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['fa-smog', 'CO₂e / kg', 'Émissions de gaz à effet de serre de la production, la transformation, le transport et l\'emballage.', 'jusqu\'à −40 pts'],
                    ['fa-droplet', 'Eau L / kg', 'Eau d\'irrigation et de transformation, rapportée au kilo de produit fini.', 'jusqu\'à −20 pts'],
                    ['fa-route', 'Distance', 'Kilomètres cumulés entre la parcelle et le point de vente, d\'après les étapes du lot.', 'jusqu\'à −20 pts'],
                    ['fa-box-open', 'Emballage', implode(' · ', $packaging).'.', '0 à −18 pts'],
                    ['fa-calendar-check', 'Saisonnalité', 'Produit récolté et vendu en pleine saison, sans stockage prolongé.', '+5 / −5 pts'],
                ] as [$icon, $title, $text, $weight])
                    <article class="nt-card flex flex-col p-5">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary/12 text-xl text-primary-strong"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 flex-1 text-sm text-muted-foreground">{{ $text }}</p>
                        <p class="mt-4 nt-badge nt-badge-earth w-fit">{{ $weight }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nt-section" aria-labelledby="formula-title">
        <div class="nt-container grid gap-10 lg:grid-cols-2">
            <div>
                <p class="nt-eyebrow mb-3">Le calcul</p>
                <h2 id="formula-title" class="text-3xl font-bold">Un score transparent et reproductible</h2>
                <p class="mt-4 text-muted-foreground">Chaque produit part de 100 points. Les pénalités sont plafonnées pour qu'aucun indicateur ne domine à lui seul ; la saisonnalité ajoute ou retire 5 points.</p>
                <pre class="relative mt-6 overflow-x-auto rounded-lg bg-earth p-5 font-mono text-sm leading-relaxed text-earth-foreground"><code>score = 100
  − min(40, CO₂e × 6)
  − min(20, eau ÷ 150)
  − min(20, distance ÷ 100)
  − pénalité emballage
  ± 5 (saisonnalité)</code></pre>
            </div>
            <x-nt.card>
                <x-slot:header>
                    <h3 class="text-base font-semibold">Exemple : {{ $example->name }}</h3>
                    <a href="{{ route('front.products.show', $example->slug) }}" class="nt-link text-sm">Voir la fiche</a>
                </x-slot:header>
                @include('partials.front.impact-summary', ['product' => $example, 'compact' => true])
            </x-nt.card>
        </div>
    </section>

    <section class="nt-section bg-surface" aria-labelledby="tips-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="Greenwashing" title="Les pièges à éviter" subtitle="Quelques signaux qui doivent éveiller votre vigilance." id="tips-title" />
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['fa-wand-magic-sparkles', 'Le chiffre magique', '« Zéro impact », « neutre en carbone » : aucun produit n\'a un impact nul. Cherchez la méthode de calcul.'],
                    ['fa-eye-slash', 'Le poste oublié', 'Une empreinte qui ignore le transport ou l\'emballage n\'est pas complète.'],
                    ['fa-leaf', 'Le vert décoratif', 'Feuilles, couleurs vertes et mots « éco » ne prouvent rien sans données vérifiables.'],
                ] as [$icon, $title, $text])
                    <div class="nt-card border-s-4 border-s-danger p-6">
                        <i class="fa-solid {{ $icon }} text-2xl text-danger-strong" aria-hidden="true"></i>
                        <h3 class="mt-3 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center">
                <x-nt.button :href="route('front.reports.create', ['type' => 'misleading_footprint'])" variant="danger" icon="fa-flag">Signaler une empreinte trompeuse</x-nt.button>
            </p>
        </div>
    </section>
@endsection
