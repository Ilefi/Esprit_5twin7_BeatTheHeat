@extends('layouts.front')

@section('title', 'Comment ça marche')

@section('hero')
    <x-nt.page-hero eyebrow="Comment ça marche" title="De la parcelle au QR code, étape par étape"
                    subtitle="Chaque acteur de la chaîne ajoute son maillon et ses preuves. NutriTrace vérifie, assemble et vous présente le parcours complet."
                    :breadcrumb="[['label' => 'Comment ça marche']]">
        <x-nt.button :href="route('front.traceability.batch', $exampleBatch->code)" icon="fa-route">Voir un exemple de lot</x-nt.button>
    </x-nt.page-hero>
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container max-w-4xl">
            <x-nt.timeline>
                @foreach ([
                    ['fa-tractor', 'Le producteur déclare sa récolte', 'Parcelle, date, variété et certificats en cours de validité sont enregistrés. Un code de lot unique est généré.', 'primary'],
                    ['fa-industry', 'Le transformateur ajoute son étape', 'Procédé, analyses de laboratoire et conditionnement sont rattachés au même lot.', 'earth'],
                    ['fa-truck', 'Le distributeur trace le transport', 'Distances, modes de transport et conditions de stockage alimentent le calcul d\'empreinte.', 'gold'],
                    ['fa-shield-halved', 'NutriTrace vérifie', 'Notre équipe contrôle les justificatifs et la cohérence des dates et des lieux. Les étapes conformes reçoivent le badge « Vérifié ».', 'primary'],
                    ['fa-qrcode', 'Vous scannez et décidez', 'Le QR code ou le numéro de lot affiche tout le parcours, l\'éco-score et les avis. Une incohérence ? Signalez-la.', 'danger'],
                ] as [$icon, $title, $text, $tone])
                    <x-nt.timeline-item :icon="$icon" :title="$title" :tone="$tone" :time="'Étape '.$loop->iteration">{{ $text }}</x-nt.timeline-item>
                @endforeach
            </x-nt.timeline>
        </div>
    </section>

    <section class="nt-section bg-surface">
        <div class="nt-container grid gap-6 md:grid-cols-3">
            @foreach ([
                ['fa-magnifying-glass', 'Chercher un lot', 'Saisissez le code imprimé sur l\'emballage.', route('front.traceability.index'), 'Tracer un lot'],
                ['fa-scale-unbalanced', 'Comparer deux produits', 'Mettez leurs empreintes côte à côte.', route('front.impact.compare'), 'Comparer'],
                ['fa-flag', 'Signaler un doute', 'Allégation, label ou chiffre suspect ?', route('front.reports.create'), 'Signaler'],
            ] as [$icon, $title, $text, $url, $cta])
                <div class="nt-card p-6">
                    <i class="fa-solid {{ $icon }} text-2xl text-primary" aria-hidden="true"></i>
                    <h2 class="mt-4 text-lg font-semibold">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    <x-nt.button :href="$url" variant="outline" size="sm" class="mt-5" icon-right="fa-arrow-right">{{ $cta }}</x-nt.button>
                </div>
            @endforeach
        </div>
    </section>
@endsection
