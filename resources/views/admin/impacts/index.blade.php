@extends('layouts.admin')

@section('title', 'Empreintes')
@section('page_title', 'Empreinte environnementale')
@section('page_subtitle', 'Indicateurs mesurés et éco-score de chaque produit.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Empreintes']]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.impacts.factors')" variant="outline" size="sm" icon="fa-table-list">Facteurs d'émission</x-nt.button>
    <x-nt.button :href="route('admin.impacts.create')" size="sm" icon="fa-plus">Nouvelle empreinte</x-nt.button>
@endsection

@section('content')
    <nav aria-label="Filtrer par éco-score" class="mb-6 grid grid-cols-3 gap-3 sm:grid-cols-6">
        <a href="{{ route('admin.impacts.index') }}" @class(['nt-card p-3 text-center transition hover:border-primary', 'ring-2 ring-primary' => ! request('eco')])>
            <span class="block font-heading text-xl font-bold">{{ $distribution->sum() }}</span><span class="text-xs text-muted-foreground">Tous</span>
        </a>
        @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
            <a href="{{ route('admin.impacts.index', ['eco' => $grade]) }}" @if (request('eco') === $grade) aria-current="true" @endif
               @class(['nt-card flex items-center justify-center gap-3 p-3 transition hover:border-primary', 'ring-2 ring-primary' => request('eco') === $grade])>
                <x-nt.eco-score :grade="$grade" size="sm" class="hidden lg:inline-flex" />
                <span class="text-center"><span class="block font-heading text-xl font-bold">{{ $distribution[$grade] ?? 0 }}</span><span class="text-xs text-muted-foreground">Note {{ $grade }}</span></span>
            </a>
        @endforeach
    </nav>

    <x-nt.data-table caption="Empreintes des produits">
        <thead><tr><th scope="col">Produit</th><th scope="col">CO₂e</th><th scope="col">Eau</th><th scope="col">Distance</th><th scope="col">Emballage</th><th scope="col">Score</th><th scope="col" class="text-right">Actions</th></tr></thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td class="font-semibold">{{ $product->name }}</td>
                    <td class="whitespace-nowrap">{{ number_format($product->impact->co2_per_kg, 1, ',', ' ') }} kg</td>
                    <td class="whitespace-nowrap">{{ number_format($product->impact->water_per_kg, 0, ',', ' ') }} L</td>
                    <td class="whitespace-nowrap">{{ $product->impact->distance_km }} km</td>
                    <td>{{ $packaging[$product->impact->packaging] }}</td>
                    <td><div class="flex items-center gap-2"><x-nt.eco-score :grade="$product->eco_score" size="sm" /><span class="text-xs text-muted-foreground">{{ $product->impact->eco_points }}</span></div></td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.impacts.edit', $product->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Modifier l'empreinte de {{ $product->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-impact-{{ $product->id }}')" aria-label="Supprimer l'empreinte de {{ $product->name }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <x-slot:footer>{{ $products->links() }}</x-slot:footer>
    </x-nt.data-table>

    @foreach ($products as $product)
        @include('partials.admin.delete-modal', ['name' => 'delete-impact-'.$product->id, 'action' => route('admin.impacts.destroy', $product->id), 'label' => 'l\'empreinte de '.$product->name])
    @endforeach
@endsection
