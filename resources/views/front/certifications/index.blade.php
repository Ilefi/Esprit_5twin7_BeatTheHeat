@extends('layouts.front')

@section('title', 'Guide des labels')
@section('meta_description', 'Ce que garantissent — et ne garantissent pas — les labels bio, local, équitable, origine protégée et sans pesticides.')

@section('hero')
    <x-nt.page-hero eyebrow="Module 1 · Certifications" title="Le guide des labels"
                    subtitle="Chaque label répond à un cahier des charges précis. Voici ce qu'il garantit vraiment, qui le délivre, et ses limites."
                    :breadcrumb="[['label' => 'Certifications']]" />
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($certifications as $certification)
                <article class="nt-card nt-card-hover relative flex flex-col">
                    <header class="flex items-start justify-between gap-3 border-b p-5">
                        <div>
                            <x-nt.cert-badge :certification="$certification" />
                            <h2 class="mt-3 text-lg font-semibold">
                                <a href="{{ route('front.certifications.show', $certification->slug) }}" class="after:absolute after:inset-0">{{ $certification->name }}</a>
                            </h2>
                            <p class="text-xs text-muted-foreground">{{ $certification->issuer }}</p>
                        </div>
                        <span class="nt-badge shrink-0">{{ $certification->products_count }} produits</span>
                    </header>
                    <div class="flex-1 space-y-4 p-5 text-sm">
                        <div>
                            <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-primary-strong"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Garantit</h3>
                            <ul class="space-y-1 text-foreground/85">
                                @foreach ($certification->guarantees as $item)
                                    <li class="flex gap-2"><span aria-hidden="true">·</span>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-warning-strong"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Ne garantit pas</h3>
                            <ul class="space-y-1 text-foreground/85">
                                @foreach ($certification->limits as $item)
                                    <li class="flex gap-2"><span aria-hidden="true">·</span>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="nt-section bg-surface" aria-labelledby="fake-title">
        <div class="nt-container grid gap-10 lg:grid-cols-[1fr_1.4fr] lg:items-start">
            <div>
                <p class="nt-eyebrow mb-3"><i class="fa-solid fa-user-secret" aria-hidden="true"></i> Vigilance</p>
                <h2 id="fake-title" class="text-3xl font-bold">Comment repérer un faux label ?</h2>
                <p class="mt-4 text-muted-foreground">Logos inventés, mentions vagues, certificats expirés… Quelques réflexes suffisent pour démasquer la plupart des faux labels.</p>
                <x-nt.button :href="route('front.reports.create', ['type' => 'dubious_certification'])" variant="danger" class="mt-6" icon="fa-flag">Signaler une certification douteuse</x-nt.button>
            </div>
            <ol class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['fa-hashtag', 'Cherchez un numéro de certificat', 'Un vrai label mentionne l\'organisme certificateur et un numéro vérifiable.'],
                    ['fa-calendar-xmark', 'Vérifiez la date de validité', 'Les certificats sont renouvelés chaque année : un certificat expiré ne vaut rien.'],
                    ['fa-spell-check', 'Méfiez-vous des mots flous', '« Naturel », « éco », « respectueux » ne sont pas des labels encadrés.'],
                    ['fa-qrcode', 'Comparez avec la traçabilité', 'Sur NutriTrace, un label vérifié apparaît sur la fiche ET dans le lot.'],
                ] as [$icon, $title, $text])
                    <li class="nt-card p-5">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-gold/25 font-heading font-bold text-gold-strong">{{ $loop->iteration }}</span>
                        <h3 class="mt-3 flex items-center gap-2 font-semibold"><i class="fa-solid {{ $icon }} text-muted-foreground" aria-hidden="true"></i>{{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endsection
