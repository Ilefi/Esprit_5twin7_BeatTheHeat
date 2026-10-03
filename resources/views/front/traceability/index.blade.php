@extends('layouts.front')

@section('title', 'Tracer un lot')
@section('meta_description', 'Retrouvez le parcours complet d\'un produit grâce à son numéro de lot ou son QR code.')

@section('hero')
    <section class="nt-gradient-hero relative overflow-hidden border-b">
        <div class="nt-pattern" aria-hidden="true"></div>
        <div class="nt-container relative py-14 text-center lg:py-20">
            @include('partials.shared.breadcrumb', ['items' => [['label' => 'Accueil', 'url' => route('front.home')], ['label' => 'Traçabilité']], 'class' => 'mb-8 flex justify-center'])
            <p class="nt-eyebrow mb-3">Module 2 · Chaîne de traçabilité</p>
            <h1 class="mx-auto max-w-3xl text-4xl font-extrabold sm:text-5xl">Quel est le parcours de votre produit ?</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">Saisissez le numéro de lot imprimé près de la date limite, ou scannez le QR code de l'emballage.</p>

            <form action="{{ route('front.traceability.index') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 rounded-xl border bg-surface p-2 shadow-lg sm:flex-row">
                <label for="lot-code" class="sr-only">Numéro de lot</label>
                <div class="relative flex-1">
                    <i class="fa-solid fa-barcode pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-lg text-muted-foreground" aria-hidden="true"></i>
                    <input id="lot-code" name="code" type="search" value="{{ old('code', request('code')) }}" placeholder="NT-2026-OLV-0412" autocomplete="off" autofocus
                           class="h-14 w-full rounded-lg bg-transparent ps-12 pe-3 font-mono text-lg uppercase tracking-wide focus:outline-none">
                </div>
                <button type="submit" class="nt-btn nt-btn-primary nt-btn-lg"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Tracer</button>
            </form>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-sm">
                <button type="button" class="nt-btn nt-btn-outline nt-btn-sm" x-data x-on:click="$dispatch('open-modal', 'scanner')">
                    <i class="fa-solid fa-camera" aria-hidden="true"></i> Scanner un QR code
                </button>
                <span class="text-muted-foreground">ou essayez :</span>
                @foreach ($batches->take(4) as $batch)
                    <a href="{{ route('front.traceability.batch', $batch->code) }}" class="nt-badge font-mono hover:bg-primary/12 hover:text-primary-strong">{{ $batch->code }}</a>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container">
            <x-nt.section-header eyebrow="La chaîne expliquée" title="Ce que vous verrez pour chaque lot" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['fa-tractor', 'Origine', 'Exploitation, parcelle, date de récolte.'],
                    ['fa-industry', 'Transformation', 'Procédés, analyses et conditionnement.'],
                    ['fa-truck', 'Transport', 'Trajets, distances et conditions de stockage.'],
                    ['fa-file-shield', 'Preuves', 'Documents justificatifs et badge « Vérifié ».'],
                ] as [$icon, $title, $text])
                    <div class="nt-card p-6 text-center">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl bg-primary/12 text-xl text-primary-strong"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nt-section bg-surface pt-12">
        <div class="nt-container">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <h2 class="text-2xl font-bold">Derniers lots tracés</h2>
                <x-nt.button :href="route('front.actors.index')" variant="outline" size="sm" icon="fa-people-group">Annuaire des acteurs</x-nt.button>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($batches as $batch)
                    <a href="{{ route('front.traceability.batch', $batch->code) }}" class="nt-card nt-card-hover flex items-center gap-4 p-4">
                        <x-nt.product-image :product="$batch->product" size="sm" class="h-16 w-16 shrink-0 rounded-lg" />
                        <div class="min-w-0 flex-1">
                            <p class="font-mono text-xs text-muted-foreground">{{ $batch->code }}</p>
                            <p class="truncate font-semibold">{{ $batch->product->name }}</p>
                            <p class="text-xs text-muted-foreground">{{ $batch->steps->count() }} étapes · {{ $batch->total_km }} km</p>
                        </div>
                        <x-status-badge type="batch" :value="$batch->status" :icon="false" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <x-nt.modal name="scanner" title="Scanner un QR code" max-width="md">
        <div class="grid place-items-center gap-4 text-center">
            <div class="relative grid h-56 w-56 place-items-center rounded-xl bg-foreground/90">
                <span class="absolute inset-6 rounded-lg border-4 border-dashed border-primary-light"></span>
                <i class="fa-solid fa-qrcode text-6xl text-surface/30" aria-hidden="true"></i>
            </div>
            <p class="text-sm text-muted-foreground">La lecture par caméra sera disponible dans l'application mobile. En attendant, saisissez le code imprimé sous le QR code.</p>
        </div>
        <x-slot:footer>
            <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Fermer</x-nt.button>
        </x-slot:footer>
    </x-nt.modal>
@endsection
