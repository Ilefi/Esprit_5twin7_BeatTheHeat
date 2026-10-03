@extends('layouts.admin')

@section('title', 'Produits')
@section('page_title', 'Produits')
@section('page_subtitle', 'Catalogue, statut de publication et éco-score.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Produits']]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.categories.index')" variant="outline" size="sm" icon="fa-tags">Catégories</x-nt.button>
    <x-nt.button :href="route('admin.products.create')" size="sm" icon="fa-plus">Nouveau produit</x-nt.button>
@endsection

@section('content')
    @include('partials.admin.filters', [
        'action' => route('admin.products.index'),
        'search' => 'Nom du produit ou producteur…',
        'selects' => [
            'categorie' => ['Catégorie', $categories],
            'statut' => ['Statut', $statuses],
            'eco' => ['Éco-score', array_combine(['A', 'B', 'C', 'D', 'E'], ['A', 'B', 'C', 'D', 'E'])],
        ],
    ])

    <div class="mt-6" x-data="{ selected: [], ids: @js($products->pluck('id')) }">
        <x-nt.data-table caption="Liste des produits">
            <x-slot:toolbar>
                <p class="text-sm text-muted-foreground" x-show="selected.length === 0">{{ $products->total() }} produits</p>
                <div class="flex flex-wrap items-center gap-2" x-show="selected.length > 0" x-cloak>
                    <span class="text-sm font-semibold" x-text="selected.length + ' sélectionné(s)'"></span>
                    <x-nt.button size="sm" variant="outline" icon="fa-eye" x-on:click="$dispatch('nt-toast', { message: selected.length + ' produit(s) publiés (démo).' }); selected = []">Publier</x-nt.button>
                    <x-nt.button size="sm" variant="outline" icon="fa-eye-slash" x-on:click="$dispatch('nt-toast', { message: selected.length + ' produit(s) dépubliés (démo).' }); selected = []">Dépublier</x-nt.button>
                    <x-nt.button size="sm" variant="ghost" x-on:click="selected = []">Annuler</x-nt.button>
                </div>
            </x-slot:toolbar>

            <thead>
                <tr>
                    <th scope="col" class="w-10">
                        <input type="checkbox" class="nt-checkbox" aria-label="Tout sélectionner"
                               :checked="selected.length === ids.length && ids.length > 0" x-on:change="selected = $event.target.checked ? [...ids] : []">
                    </th>
                    <th scope="col">Produit</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Certifications</th>
                    <th scope="col">Éco-score</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr :class="selected.includes({{ $product->id }}) && 'bg-primary/6'">
                        <td><input type="checkbox" class="nt-checkbox" value="{{ $product->id }}" x-model.number="selected" aria-label="Sélectionner {{ $product->name }}"></td>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-nt.product-image :product="$product" size="sm" class="h-11 w-11 shrink-0 rounded-lg" />
                                <div class="min-w-0">
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="block max-w-56 truncate font-semibold hover:text-primary-strong">{{ $product->name }}</a>
                                    <p class="text-xs text-muted-foreground">{{ $product->producer->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap">{{ $product->category->name }}</td>
                        <td><div class="flex flex-wrap gap-1">@foreach ($product->certifications as $certification)<x-nt.cert-badge :certification="$certification" />@endforeach</div></td>
                        <td><x-nt.eco-score :grade="$product->eco_score" size="sm" /></td>
                        <td><x-status-badge type="product" :value="$product->status" /></td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.products.show', $product->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Voir {{ $product->name }}" title="Voir"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Modifier {{ $product->name }}" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')" aria-label="Supprimer {{ $product->name }}" title="Supprimer"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-nt.empty-state icon="fa-basket-shopping" title="Aucun produit" description="Modifiez les filtres ou créez un produit." class="border-0" /></td></tr>
                @endforelse
            </tbody>

            <x-slot:footer>{{ $products->links() }}</x-slot:footer>
        </x-nt.data-table>
    </div>

    @foreach ($products as $product)
        @include('partials.admin.delete-modal', ['name' => 'delete-product-'.$product->id, 'action' => route('admin.products.destroy', $product->id), 'label' => $product->name])
    @endforeach
@endsection
