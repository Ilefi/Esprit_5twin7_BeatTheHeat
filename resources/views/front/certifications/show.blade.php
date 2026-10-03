@extends('layouts.front')

@section('title', $certification->name)
@section('meta_description', $certification->description)

@section('hero')
    <x-nt.page-hero :eyebrow="'Label · '.$certification->short_name" :title="$certification->name" :subtitle="$certification->description"
                    :breadcrumb="[['label' => 'Certifications', 'url' => route('front.certifications.index')], ['label' => $certification->name]]">
        <x-nt.button :href="route('front.reports.create', ['cible' => 'certification', 'id' => $certification->id])" variant="outline" icon="fa-flag">Signaler un usage abusif</x-nt.button>
        <x-slot:aside>
            <div class="nt-card flex items-center gap-4 p-5">
                <x-nt.cert-badge :certification="$certification" class="px-3 py-1.5 text-sm" />
                <div class="text-sm">
                    <p class="font-semibold">{{ $certification->products_count }} produits · {{ $certification->actors_count }} acteurs</p>
                    <p class="text-muted-foreground">{{ $certification->issuer }}</p>
                </div>
            </div>
        </x-slot:aside>
    </x-nt.page-hero>
@endsection

@section('content')
    <section class="nt-container grid gap-8 py-10 lg:grid-cols-3">
        <x-nt.card class="lg:col-span-1">
            <x-slot:header><h2 class="text-base font-semibold"><i class="fa-solid fa-list-check me-2 text-primary" aria-hidden="true"></i>Critères du cahier des charges</h2></x-slot:header>
            <ul class="space-y-3 text-sm">
                @foreach ($certification->criteria as $criterion)
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-primary" aria-hidden="true"></i>{{ $criterion }}</li>
                @endforeach
            </ul>
        </x-nt.card>
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold text-primary-strong"><i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i>Ce qu'il garantit</h2></x-slot:header>
            <ul class="space-y-2 text-sm">@foreach ($certification->guarantees as $item)<li>· {{ $item }}</li>@endforeach</ul>
        </x-nt.card>
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold text-warning-strong"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>Ses limites</h2></x-slot:header>
            <ul class="space-y-2 text-sm">@foreach ($certification->limits as $item)<li>· {{ $item }}</li>@endforeach</ul>
        </x-nt.card>
    </section>

    <section class="nt-container pb-12" aria-labelledby="cert-products">
        <h2 id="cert-products" class="mb-6 text-2xl font-bold">Produits certifiés</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-nt.product-card :product="$product" />
            @empty
                <x-nt.empty-state icon="fa-basket-shopping" title="Aucun produit pour ce label" class="sm:col-span-2 lg:col-span-4" />
            @endforelse
        </div>

        @if ($actors->isNotEmpty())
            <h2 class="mb-6 mt-12 text-2xl font-bold">Acteurs certifiés</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($actors as $actor)
                    <x-nt.actor-card :actor="$actor" />
                @endforeach
            </div>
        @endif

        <div class="mt-12 flex flex-wrap items-center gap-2 border-t pt-6">
            <span class="me-2 text-sm text-muted-foreground">Autres labels :</span>
            @foreach ($others as $other)
                <a href="{{ route('front.certifications.show', $other->slug) }}"><x-nt.cert-badge :certification="$other" /></a>
            @endforeach
        </div>
    </section>
@endsection
