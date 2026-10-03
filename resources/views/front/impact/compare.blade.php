@extends('layouts.front')

@section('title', 'Comparer l\'empreinte')

@php
    $rows = [
        ['Éco-score', fn ($p) => $p->impact->eco_points, 'pts', 'desc'],
        ['Émissions', fn ($p) => $p->impact->co2_per_kg, 'kg CO₂e/kg', 'asc'],
        ['Eau', fn ($p) => $p->impact->water_per_kg, 'L/kg', 'asc'],
        ['Distance', fn ($p) => $p->impact->distance_km, 'km', 'asc'],
    ];
@endphp

@section('hero')
    <x-nt.page-hero eyebrow="Module 3 · Comparateur" title="Comparer l'empreinte de 2 à 3 produits"
                    subtitle="Choisissez les produits à mettre côte à côte : le meilleur choix environnemental est mis en évidence."
                    :breadcrumb="[['label' => 'Empreinte', 'url' => route('front.impact.index')], ['label' => 'Comparer']]" />
@endsection

@section('content')
    <section class="nt-container py-10">
        <form method="GET" action="{{ route('front.impact.compare') }}" class="nt-card grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
            @for ($i = 0; $i < 3; $i++)
                <div>
                    <label for="compare-{{ $i }}" class="nt-label">Produit {{ $i + 1 }} @if ($i === 2)<span class="font-normal text-muted-foreground">(facultatif)</span>@endif</label>
                    <select id="compare-{{ $i }}" name="produits[]" class="nt-input">
                        <option value="">— Aucun —</option>
                        @foreach ($products as $option)
                            <option value="{{ $option->id }}" @selected($selected->get($i)?->id === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endfor
            <x-nt.button type="submit" icon="fa-scale-unbalanced">Comparer</x-nt.button>
        </form>

        @if ($best)
            <div class="mt-8 flex flex-col items-start gap-4 rounded-xl border-2 border-primary bg-primary/6 p-5 sm:flex-row sm:items-center" role="status">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-primary text-xl text-primary-foreground"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                <div class="flex-1">
                    <p class="font-heading text-lg font-semibold">Verdict : {{ $best->name }}</p>
                    <p class="text-sm text-muted-foreground">Meilleur éco-score de la sélection ({{ $best->impact->eco_points }}/100, note {{ $best->eco_score }}).</p>
                </div>
                <x-nt.button :href="route('front.products.show', $best->slug)" variant="outline" size="sm">Voir le produit</x-nt.button>
            </div>
        @endif

        <div class="mt-8 grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr))">
            @foreach ($selected as $product)
                <article @class(['nt-card p-5', 'ring-2 ring-primary' => $best?->id === $product->id])>
                    <div class="flex items-center gap-3">
                        <x-nt.product-image :product="$product" size="sm" class="h-14 w-14 shrink-0 rounded-lg" />
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold"><a href="{{ route('front.products.show', $product->slug) }}" class="hover:text-primary-strong">{{ $product->name }}</a></h2>
                            <p class="text-xs text-muted-foreground">{{ $product->producer->name }}</p>
                        </div>
                    </div>
                    <x-nt.eco-score :grade="$product->eco_score" class="mt-4" show-label />
                    @if ($best?->id === $product->id)
                        <x-nt.badge variant="solid" icon="fa-trophy" class="mt-3">Meilleur choix</x-nt.badge>
                    @endif
                </article>
            @endforeach
        </div>

        <x-nt.data-table title="Indicateurs côte à côte" caption="Comparaison des indicateurs d'empreinte" class="mt-8">
            <thead>
                <tr>
                    <th scope="col">Indicateur</th>
                    @foreach ($selected as $product)
                        <th scope="col">{{ $product->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as [$label, $value, $unit, $direction])
                    @php
                        $values = $selected->map($value);
                        $winner = $direction === 'asc' ? $values->min() : $values->max();
                    @endphp
                    <tr>
                        <th scope="row" class="font-medium">{{ $label }}</th>
                        @foreach ($selected as $product)
                            <td @class(['font-semibold text-primary-strong' => $value($product) == $winner])>
                                {{ number_format($value($product), $value($product) < 10 ? 1 : 0, ',', ' ') }} {{ $unit }}
                                @if ($value($product) == $winner)<i class="fa-solid fa-circle-check ms-1" aria-label="Meilleure valeur"></i>@endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <th scope="row" class="font-medium">Emballage</th>
                    @foreach ($selected as $product)<td>{{ $packaging[$product->impact->packaging] }}</td>@endforeach
                </tr>
                <tr>
                    <th scope="row" class="font-medium">Saisonnalité</th>
                    @foreach ($selected as $product)<td>{{ $product->impact->seasonal ? 'De saison' : 'Hors saison' }}</td>@endforeach
                </tr>
            </tbody>
        </x-nt.data-table>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            @foreach ([['Émissions (kg CO₂e / kg)', 'co2_per_kg', 'danger'], ['Eau (L / kg)', 'water_per_kg', 'info']] as [$title, $field, $color])
                <figure class="nt-card p-5">
                    <figcaption class="mb-3 text-sm font-semibold">{{ $title }}</figcaption>
                    <div class="h-64">
                        <canvas x-data="ntChart(@js([
                            'type' => 'bar',
                            'labels' => $selected->pluck('name')->all(),
                            'datasets' => [['label' => $title, 'data' => $selected->map(fn ($p) => $p->impact->{$field})->all(), 'color' => $color]],
                            'legend' => false,
                        ]))" role="img" aria-label="{{ $title }} : {{ $selected->map(fn ($p) => $p->name.' '.$p->impact->{$field})->implode(', ') }}"></canvas>
                    </div>
                </figure>
            @endforeach
        </div>
    </section>
@endsection
