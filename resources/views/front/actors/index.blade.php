@extends('layouts.front')

@section('title', 'Annuaire des acteurs')

@section('hero')
    <x-nt.page-hero eyebrow="Module 2 · Acteurs de la chaîne" title="Ceux qui font vos produits"
                    subtitle="Producteurs, transformateurs et distributeurs engagés dans la transparence."
                    :breadcrumb="[['label' => 'Traçabilité', 'url' => route('front.traceability.index')], ['label' => 'Acteurs']]" />
@endsection

@section('content')
    <section class="nt-container py-10">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <nav aria-label="Filtrer par type" class="relative -mx-4 overflow-x-auto px-4">
                <ul class="flex min-w-max gap-2">
                    <li>
                        <a href="{{ route('front.actors.index', request()->only('q')) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => ! request('type'), 'nt-btn-outline' => request('type')])>
                            Tous <span class="opacity-80">({{ $counts->sum() }})</span>
                        </a>
                    </li>
                    @foreach ($types as $value => $label)
                        <li>
                            <a href="{{ route('front.actors.index', ['type' => $value] + request()->only('q')) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => request('type') === $value, 'nt-btn-outline' => request('type') !== $value])
                               @if (request('type') === $value) aria-current="true" @endif>
                                <i class="fa-solid {{ \App\View\Components\StatusBadge::iconFor('actor_type', $value) }}" aria-hidden="true"></i>
                                {{ $label }}s <span class="opacity-80">({{ $counts[$value] ?? 0 }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <form method="GET" action="{{ route('front.actors.index') }}" role="search" class="relative w-full lg:w-80">
                @if (request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
                <label for="actor-search" class="sr-only">Rechercher un acteur</label>
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground" aria-hidden="true"></i>
                <input id="actor-search" type="search" name="q" value="{{ request('q') }}" placeholder="Nom, ville, région…" class="nt-input ps-10">
            </form>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($actors as $actor)
                <x-nt.actor-card :actor="$actor" />
            @empty
                <x-nt.empty-state icon="fa-people-group" title="Aucun acteur trouvé" class="sm:col-span-2 lg:col-span-3" />
            @endforelse
        </div>

        <div class="mt-10">{{ $actors->links() }}</div>
    </section>
@endsection
