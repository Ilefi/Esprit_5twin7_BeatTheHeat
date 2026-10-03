@extends('layouts.front')

@section('title', 'À propos')
@section('meta_description', 'La mission de NutriTrace : rendre la chaîne alimentaire transparente, de la ferme à l\'assiette.')

@section('hero')
    <x-nt.page-hero eyebrow="Notre mission" title="Rendre visible ce qui se passe avant l'assiette"
                    subtitle="NutriTrace est né d'un constat simple : on sait rarement d'où vient ce que l'on mange, et les promesses vertes sont difficiles à vérifier."
                    :breadcrumb="[['label' => 'À propos']]" />
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container grid gap-12 lg:grid-cols-2 lg:items-center">
            <div class="space-y-5 text-lg text-muted-foreground">
                <h2 class="text-3xl font-bold text-foreground">Une plateforme, quatre engagements</h2>
                <p>Nous relions producteurs, transformateurs, distributeurs et consommateurs autour d'une même source d'information : la donnée de traçabilité, vérifiée et partagée.</p>
                <p>Chaque produit affiche son origine, ses étapes, ses labels contrôlés et son empreinte environnementale. Chaque consommateur peut noter, questionner et signaler.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['fa-eye', 'Transparence', 'Des données ouvertes et compréhensibles, sans jargon.'],
                    ['fa-scale-balanced', 'Équité', 'Une juste valorisation du travail des producteurs.'],
                    ['fa-earth-africa', 'Sobriété', 'Mesurer l\'impact pour mieux le réduire.'],
                    ['fa-users', 'Participation', 'Les consommateurs vérifient et alertent.'],
                ] as [$icon, $title, $text])
                    <div class="nt-card p-5">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-primary/12 text-primary-strong"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nt-section bg-surface" aria-labelledby="team-title">
        <div class="nt-container">
            <x-nt.section-header eyebrow="L'équipe projet" title="Quatre modules, quatre responsables" subtitle="Projet académique — Esprit, 5TWIN, Applications Web Avancées 2026-2027." id="team-title" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Produits & Certifications', 'fa-basket-shopping', 'bg-primary/12 text-primary-strong'],
                    ['Chaîne de traçabilité', 'fa-route', 'bg-earth/12 text-earth'],
                    ['Empreinte environnementale', 'fa-leaf', 'bg-eco-b/20 text-eco-b-strong'],
                    ['Signalements & Avis', 'fa-bullhorn', 'bg-danger/10 text-danger-strong'],
                ] as [$module, $icon, $tint])
                    <article class="nt-card p-6 text-center">
                        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full text-3xl {{ $tint }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <p class="mt-4 font-heading text-xs font-semibold uppercase tracking-wider text-muted-foreground">Module {{ $loop->iteration }}</p>
                        <h3 class="mt-1 font-semibold">{{ $module }}</h3>
                        <p class="mt-2 text-muted-foreground">[Nom]</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
