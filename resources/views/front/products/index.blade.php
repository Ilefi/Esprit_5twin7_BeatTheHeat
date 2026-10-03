@extends('layouts.front')

@section('title', 'Produits tracés')
@section('meta_description', 'Catalogue NutriTrace : produits tunisiens tracés de la ferme à l\'assiette, avec labels vérifiés et éco-score.')

@section('hero')
    <x-nt.page-hero eyebrow="Module 1 · Produits & Certifications" title="Le catalogue des produits tracés"
                    subtitle="Filtrez par catégorie, label ou éco-score : chaque fiche affiche l'origine, les certifications vérifiées et l'empreinte."
                    :breadcrumb="[['label' => 'Produits']]" />
@endsection

@section('content')
    <section class="nt-container py-10" x-data="{ filters: false }">
        <div class="grid gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">
            {{-- Desktop sidebar --}}
            <aside class="hidden lg:block" aria-label="Filtres">
                <div class="nt-card sticky top-24 p-5">
                    <h2 class="mb-5 flex items-center gap-2 text-base font-semibold"><i class="fa-solid fa-sliders text-primary" aria-hidden="true"></i> Filtres</h2>
                    @include('partials.front.product.filters', ['prefix' => 'desktop'])
                </div>
            </aside>

            {{-- Mobile off-canvas --}}
            <div x-show="filters" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Filtres">
                <div x-show="filters" x-transition.opacity class="absolute inset-0 bg-foreground/50" x-on:click="filters = false"></div>
                <div x-show="filters" x-on:keydown.escape.window="filters = false"
                     x-transition:enter="transition duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                     class="absolute inset-y-0 left-0 w-full max-w-xs overflow-y-auto bg-surface p-5 shadow-lg">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-base font-semibold">Filtres</h2>
                        <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon" x-on:click="filters = false" aria-label="Fermer les filtres"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    </div>
                    @include('partials.front.product.filters', ['prefix' => 'mobile'])
                </div>
            </div>

            <div class="min-w-0">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('front.products.index') }}" role="search" class="relative flex-1">
                        @foreach (request()->except(['q', 'page']) as $key => $value)
                            @foreach ((array) $value as $item)
                                <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $item }}">
                            @endforeach
                        @endforeach
                        <label for="catalogue-search" class="sr-only">Rechercher un produit</label>
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground" aria-hidden="true"></i>
                        <input id="catalogue-search" type="search" name="q" value="{{ request('q') }}" placeholder="Huile d'olive, Tozeur, miel…" class="nt-input ps-10">
                    </form>
                    <div class="flex items-center gap-2">
                        <button type="button" class="nt-btn nt-btn-outline nt-btn-sm lg:hidden" x-on:click="filters = true"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filtres</button>
                        <div class="inline-flex rounded-lg border bg-surface p-1" role="group" aria-label="Affichage">
                            <a href="{{ request()->fullUrlWithQuery(['vue' => 'grille']) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => $layout === 'grid', 'nt-btn-ghost' => $layout !== 'grid']) aria-label="Affichage en grille" @if ($layout === 'grid') aria-current="true" @endif>
                                <i class="fa-solid fa-grip" aria-hidden="true"></i>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['vue' => 'liste']) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => $layout === 'list', 'nt-btn-ghost' => $layout !== 'list']) aria-label="Affichage en liste" @if ($layout === 'list') aria-current="true" @endif>
                                <i class="fa-solid fa-list" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <p class="mb-5 text-sm text-muted-foreground" aria-live="polite">
                    <strong class="text-foreground">{{ $products->total() }}</strong> produit{{ $products->total() > 1 ? 's' : '' }} trouvé{{ $products->total() > 1 ? 's' : '' }}
                    @if (request('q')) pour « {{ request('q') }} » @endif
                </p>

                @forelse ($products as $product)
                    @if ($loop->first)
                        <div @class(['grid gap-6', 'sm:grid-cols-2 xl:grid-cols-3' => $layout === 'grid'])>
                    @endif
                    <x-nt.product-card :product="$product" :layout="$layout" />
                    @if ($loop->last)
                        </div>
                    @endif
                @empty
                    <x-nt.empty-state icon="fa-basket-shopping" title="Aucun produit ne correspond"
                                      description="Essayez d'élargir vos filtres ou de modifier votre recherche.">
                        <x-nt.button :href="route('front.products.index')" variant="outline" icon="fa-rotate-left">Réinitialiser les filtres</x-nt.button>
                    </x-nt.empty-state>
                @endforelse

                <div class="mt-10">{{ $products->links() }}</div>
            </div>
        </div>
    </section>
@endsection
