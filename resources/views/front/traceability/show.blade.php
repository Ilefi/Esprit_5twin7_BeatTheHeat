@extends('layouts.front')

@section('title', 'Lot '.$batch->code)
@section('meta_description', 'Parcours complet du lot '.$batch->code.' : '.$batch->product->name.', de la ferme à l\'assiette.')

{{-- Printable lot sheet: hide site chrome when printing. --}}
@push('styles')
    <style media="print">
        header, footer, .no-print { display: none !important; }
        .nt-card { box-shadow: none !important; }
    </style>
@endpush

@php
    $stageIcons = ['production' => 'fa-tractor', 'processing' => 'fa-industry', 'distribution' => 'fa-truck', 'consumer' => 'fa-utensils'];
    $stageLabels = \App\Http\Controllers\Admin\BatchController::STAGES;
    $stage = (string) request('etape');
    $steps = $batch->steps->when(array_key_exists($stage, $stageLabels), fn ($c) => $c->where('stage', $stage)->values());
    // Route diagram: x position proportional to cumulative distance.
    $cumulative = 0;
    // Steps at the same place (0 km between them) share one point, labelled with the last one.
    $points = $batch->steps->map(function ($step) use (&$cumulative) {
        $cumulative += $step->distance_km;
        return ['km' => $cumulative, 'step' => $step];
    })->groupBy('km')->map->last()->values();
@endphp

@section('content')
    <section class="nt-gradient-hero relative overflow-hidden border-b">
        <div class="nt-pattern" aria-hidden="true"></div>
        <div class="nt-container relative py-8 lg:py-10">
            @include('partials.shared.breadcrumb', ['items' => [
                ['label' => 'Accueil', 'url' => route('front.home')],
                ['label' => 'Traçabilité', 'url' => route('front.traceability.index')],
                ['label' => $batch->code],
            ], 'class' => 'mb-6 no-print'])

            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-5">
                    <x-nt.product-image :product="$batch->product" size="sm" class="h-20 w-20 shrink-0 rounded-xl border" />
                    <div>
                        <p class="font-mono text-sm text-muted-foreground">Lot {{ $batch->code }}</p>
                        <h1 class="text-2xl font-extrabold sm:text-3xl">{{ $batch->product->name }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-status-badge type="batch" :value="$batch->status" />
                            <x-nt.eco-score :grade="$batch->product->eco_score" size="sm" />
                            <span class="text-sm text-muted-foreground">{{ $batch->quantity }}</span>
                        </div>
                    </div>
                </div>
                <dl class="grid grid-cols-3 gap-3 text-center">
                    @foreach ([['Étapes', $batch->steps->count()], ['Acteurs', $batch->actors_count], ['Distance', $batch->total_km.' km']] as [$label, $value])
                        <div class="nt-card px-4 py-3">
                            <dd class="font-heading text-xl font-bold">{{ $value }}</dd>
                            <dt class="text-xs text-muted-foreground">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    <section class="nt-container py-10" aria-labelledby="journey-title">
        <h2 id="journey-title" class="mb-6 text-2xl font-bold">Le parcours du lot</h2>
        <div class="no-print mb-6 flex flex-wrap gap-2">
            <a href="{{ route('front.traceability.batch', $batch->code) }}" class="nt-btn nt-btn-sm {{ array_key_exists($stage, $stageLabels) ? 'nt-btn-outline' : 'nt-btn-primary' }}">Tous</a>
            @foreach ($stageLabels as $key => $label)
                <a href="{{ route('front.traceability.batch', ['code' => $batch->code, 'etape' => $key]) }}" class="nt-btn nt-btn-sm {{ $stage === $key ? 'nt-btn-primary' : 'nt-btn-outline' }}">{{ $label }}</a>
            @endforeach
        </div>
        @if ($steps->isEmpty())
            <p class="text-sm text-muted-foreground">Aucune étape pour ce filtre.</p>
        @endif
        {{-- Desktop: horizontal stepper --}}
        <ol class="relative hidden gap-4 lg:grid" style="grid-template-columns: repeat({{ max(1, $steps->count()) }}, minmax(0, 1fr))">
            <span class="absolute left-[8%] right-[8%] top-7 h-0.5 border-t-2 border-dashed border-primary/40" aria-hidden="true"></span>
            @foreach ($steps as $step)
                <li class="relative flex flex-col">
                    <span @class([
                        'relative z-10 mx-auto grid h-14 w-14 place-items-center rounded-full text-lg shadow-md ring-8 ring-background',
                        'bg-primary text-primary-foreground' => $step->verified,
                        'bg-surface text-muted-foreground ring-border' => ! $step->verified,
                    ])>
                        <i class="fa-solid {{ $stageIcons[$step->stage] }}" aria-hidden="true"></i>
                    </span>
                    <div class="nt-card mt-4 flex flex-1 flex-col p-4 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $stageLabels[$step->stage] }}</p>
                        <h3 class="mt-1 text-base font-semibold leading-snug">{{ $step->title }}</h3>
                        @include('front.traceability._step-details', ['step' => $step])
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- Mobile: vertical timeline --}}
        <x-nt.timeline class="lg:hidden">
            @foreach ($steps as $step)
                <x-nt.timeline-item :icon="$stageIcons[$step->stage]" :title="$step->title" :time="$stageLabels[$step->stage]" :tone="$step->verified ? 'primary' : 'muted'">
                    @include('front.traceability._step-details', ['step' => $step])
                </x-nt.timeline-item>
            @endforeach
        </x-nt.timeline>
    </section>

    <section class="nt-container grid items-start gap-6 pb-14 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <x-nt.card>
            <x-slot:header>
                <h2 class="text-base font-semibold"><i class="fa-solid fa-map-location-dot me-2 text-primary" aria-hidden="true"></i>Trajet schématique</h2>
                <span class="nt-badge nt-badge-primary">{{ $batch->total_km }} km au total</span>
            </x-slot:header>
            <div class="relative -mx-5 overflow-x-auto px-5 sm:mx-0 sm:px-0">
            <svg viewBox="0 0 800 170" class="h-auto w-full min-w-[40rem]" role="img" aria-label="Trajet de {{ $batch->total_km }} km en {{ $batch->steps->count() }} étapes">
                <line x1="40" y1="70" x2="760" y2="70" class="stroke-border" stroke-width="6" stroke-linecap="round"/>
                <line x1="40" y1="70" x2="760" y2="70" class="nt-draw-line stroke-primary" stroke-width="6" stroke-linecap="round" pathLength="1000" style="--nt-path-length: 1000"/>
                @foreach ($points as $point)
                    @php
                        // Evenly spaced stops (readable even when two stops are a few km apart); labels carry the distance.
                        $x = $points->count() > 1 ? 40 + 720 * ($loop->index / ($points->count() - 1)) : 400;
                        $anchor = $x < 140 ? 'start' : ($x > 660 ? 'end' : 'middle');
                        $dx = ['start' => -12, 'end' => 12, 'middle' => 0][$anchor];
                    @endphp
                    <g transform="translate({{ round($x, 1) }} 70)">
                        <circle r="13" @class(['stroke-surface', 'fill-primary' => $point['step']->verified, 'fill-muted-foreground' => ! $point['step']->verified]) stroke-width="4"/>
                        <text x="{{ $dx }}" y="{{ $loop->even ? 42 : -26 }}" text-anchor="{{ $anchor }}" class="fill-foreground text-[15px] font-semibold">{{ \Illuminate\Support\Str::limit($point['step']->location, 22) }}</text>
                        <text x="{{ $dx }}" y="{{ $loop->even ? 62 : -46 }}" text-anchor="{{ $anchor }}" class="fill-muted-foreground text-[13px]">{{ $point['km'] }} km</text>
                    </g>
                @endforeach
            </svg>
            </div>
        </x-nt.card>

        <x-nt.card class="text-center">
            <x-slot:header><h2 class="text-base font-semibold"><i class="fa-solid fa-qrcode me-2 text-primary" aria-hidden="true"></i>QR code du lot</h2></x-slot:header>
            <div class="mx-auto h-44 w-44 text-foreground" x-data="ntQr(@js(route('front.traceability.batch', $batch->code)))"></div>
            <p class="mt-3 font-mono text-sm">{{ $batch->code }}</p>
            <x-slot:footer class="no-print justify-center">
                <x-nt.button variant="outline" size="sm" icon="fa-print" x-data x-on:click="window.print()">Imprimer la fiche</x-nt.button>
                <x-nt.button variant="ghost" size="sm" icon="fa-link" x-data
                             x-on:click="navigator.clipboard?.writeText(window.location.href); $dispatch('nt-toast', { message: 'Lien du lot copié.' })">Copier le lien</x-nt.button>
            </x-slot:footer>
        </x-nt.card>
    </section>

    <section class="no-print border-t bg-surface">
        <div class="nt-container flex flex-col items-center justify-between gap-4 py-8 sm:flex-row">
            <p class="text-sm text-muted-foreground"><i class="fa-solid fa-circle-info me-2 text-info-strong" aria-hidden="true"></i>Une date, un lieu ou un acteur vous semble incohérent ?</p>
            <div class="flex flex-wrap gap-3">
                <x-nt.button :href="route('front.products.show', $batch->product->slug)" variant="outline" icon="fa-basket-shopping">Fiche produit</x-nt.button>
                <x-nt.button :href="route('front.reports.create', ['type' => 'traceability_error', 'cible' => 'product', 'id' => $batch->product->id])" variant="danger" icon="fa-flag">Signaler une incohérence</x-nt.button>
            </div>
        </div>
    </section>
@endsection
