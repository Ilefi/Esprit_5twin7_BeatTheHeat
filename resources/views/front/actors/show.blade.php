@extends('layouts.front')

@section('title', $actor->name)
@section('meta_description', $actor->description)

@section('hero')
    <x-nt.page-hero :eyebrow="\App\View\Components\StatusBadge::labelFor('actor_type', $actor->type).' · '.$actor->city" :title="$actor->name" :subtitle="$actor->description"
                    :breadcrumb="[['label' => 'Acteurs', 'url' => route('front.actors.index')], ['label' => $actor->name]]">
        @if ($actor->verified)
            <x-nt.badge variant="success" icon="fa-circle-check" class="px-3 py-1.5 text-sm">Acteur vérifié par NutriTrace</x-nt.badge>
        @endif
        <x-nt.button :href="route('front.reports.create', ['cible' => 'actor', 'id' => $actor->id])" variant="ghost" size="sm" icon="fa-flag" class="text-danger-strong">Signaler</x-nt.button>
    </x-nt.page-hero>
@endsection

@section('content')
    <section class="nt-container grid gap-8 py-10 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="space-y-6">
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Fiche d'identité</h2></x-slot:header>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Type</dt><dd><x-status-badge type="actor_type" :value="$actor->type" /></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Localisation</dt><dd class="text-right font-medium">{{ $actor->city }}, {{ $actor->region }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Fondé en</dt><dd class="font-medium">{{ $actor->founded_year }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Lots tracés</dt><dd class="font-medium">{{ $actor->batches_count }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Contact</dt><dd class="truncate font-medium">{{ $actor->email }}</dd></div>
                </dl>
            </x-nt.card>

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Certifications</h2></x-slot:header>
                <div class="flex flex-wrap gap-2">
                    @forelse ($actor->certifications as $certification)
                        <a href="{{ route('front.certifications.show', $certification->slug) }}"><x-nt.cert-badge :certification="$certification" full /></a>
                    @empty
                        <p class="text-sm text-muted-foreground">Aucune certification vérifiée.</p>
                    @endforelse
                </div>
            </x-nt.card>
        </aside>

        <div class="min-w-0 space-y-10">
            <div>
                <h2 class="mb-5 text-2xl font-bold">Produits</h2>
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($products as $product)
                        <x-nt.product-card :product="$product" />
                    @empty
                        <x-nt.empty-state icon="fa-basket-shopping" title="Aucun produit en propre" description="Cet acteur intervient sur des lots d'autres producteurs." class="sm:col-span-2 xl:col-span-3" />
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="mb-5 text-2xl font-bold">Lots récents</h2>
                <x-nt.data-table caption="Lots auxquels cet acteur a participé">
                    <thead><tr><th scope="col">Lot</th><th scope="col">Produit</th><th scope="col">Étapes</th><th scope="col">Statut</th></tr></thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td><a href="{{ route('front.traceability.batch', $batch->code) }}" class="nt-link font-mono text-xs">{{ $batch->code }}</a></td>
                                <td>{{ $batch->product->name }}</td>
                                <td>{{ $batch->steps->count() }}</td>
                                <td><x-status-badge type="batch" :value="$batch->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-muted-foreground">Aucun lot pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </x-nt.data-table>
            </div>
        </div>
    </section>
@endsection
