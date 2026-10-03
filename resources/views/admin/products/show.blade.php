@extends('layouts.admin')

@section('title', $product->name)
@section('page_title', $product->name)
@section('page_subtitle', $product->category->name.' · '.$product->producer->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Produits', 'url' => route('admin.products.index')], ['label' => $product->name]]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('front.products.show', $product->slug)" variant="ghost" size="sm" icon="fa-arrow-up-right-from-square">Voir en ligne</x-nt.button>
    <x-nt.button :href="route('admin.products.edit', $product->id)" variant="outline" size="sm" icon="fa-pen">Modifier</x-nt.button>
    <x-nt.button variant="danger" size="sm" icon="fa-trash" x-data x-on:click="$dispatch('open-modal', 'delete-product')">Supprimer</x-nt.button>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <div class="space-y-6">
            <x-nt.card :padding="false" class="overflow-hidden">
                <x-nt.product-image :product="$product" class="aspect-[4/3]" />
                <div class="space-y-4 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-status-badge type="product" :value="$product->status" />
                        <span class="font-heading text-xl font-bold">{{ number_format($product->price, 2, ',', ' ') }} DT</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        @foreach (['Région' => $product->region, 'Format' => $product->format, 'Transformateur' => $product->processor?->name ?? '—', 'Créé le' => $product->created_at->translatedFormat('d M Y')] as $label => $value)
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">{{ $label }}</dt><dd class="text-right font-medium">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                    <div class="flex flex-wrap gap-1.5">@foreach ($product->certifications as $certification)<x-nt.cert-badge :certification="$certification" full />@endforeach</div>
                </div>
            </x-nt.card>
        </div>

        <div class="min-w-0 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-nt.stat-card icon="fa-star" :value="number_format($product->rating_avg, 1, ',', ' ').' / 5'" :label="$product->reviews_count.' avis publiés'" tone="gold" />
                <x-nt.stat-card icon="fa-boxes-stacked" :value="$batches->count()" label="lots tracés" tone="info" />
                <x-nt.stat-card icon="fa-flag" :value="$reports->count()" label="signalements" tone="danger" />
            </div>

            <x-nt.card>
                <x-slot:header>
                    <h2 class="text-base font-semibold">Empreinte environnementale</h2>
                    <a href="{{ route('admin.impacts.edit', $product->id) }}" class="nt-link text-sm">Modifier</a>
                </x-slot:header>
                @include('partials.front.impact-summary', ['product' => $product, 'compact' => true])
            </x-nt.card>

            <x-nt.data-table title="Lots" caption="Lots de ce produit">
                <thead><tr><th scope="col">Code</th><th scope="col">Quantité</th><th scope="col">Étapes</th><th scope="col">Statut</th></tr></thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td><a href="{{ route('admin.batches.show', $batch->id) }}" class="nt-link font-mono text-xs">{{ $batch->code }}</a></td>
                            <td>{{ $batch->quantity }}</td>
                            <td>{{ $batch->steps->count() }}</td>
                            <td><x-status-badge type="batch" :value="$batch->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-muted-foreground">Aucun lot.</td></tr>
                    @endforelse
                </tbody>
            </x-nt.data-table>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-nt.card>
                    <x-slot:header><h2 class="text-base font-semibold">Derniers avis</h2><a href="{{ route('admin.reviews.index', ['produit' => $product->id]) }}" class="nt-link text-sm">Tous</a></x-slot:header>
                    <ul class="divide-y">
                        @forelse ($reviews as $review)
                            <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="block truncate text-sm font-medium hover:text-primary-strong">{{ $review->title }}</a>
                                    <x-nt.rating-stars :rating="$review->rating" size="xs" />
                                </div>
                                <x-status-badge type="review" :value="$review->status" :icon="false" />
                            </li>
                        @empty
                            <li class="text-sm text-muted-foreground">Aucun avis.</li>
                        @endforelse
                    </ul>
                </x-nt.card>
                <x-nt.card>
                    <x-slot:header><h2 class="text-base font-semibold">Signalements</h2></x-slot:header>
                    <ul class="divide-y">
                        @forelse ($reports as $report)
                            <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                                <a href="{{ route('admin.reports.show', $report->ref) }}" class="min-w-0 flex-1 truncate text-sm font-medium hover:text-primary-strong">{{ $report->title }}</a>
                                <x-status-badge type="report" :value="$report->status" :icon="false" />
                            </li>
                        @empty
                            <li class="text-sm text-muted-foreground">Aucun signalement.</li>
                        @endforelse
                    </ul>
                </x-nt.card>
            </div>
        </div>
    </div>

    @include('partials.admin.delete-modal', ['name' => 'delete-product', 'action' => route('admin.products.destroy', $product->id), 'label' => $product->name])
@endsection
