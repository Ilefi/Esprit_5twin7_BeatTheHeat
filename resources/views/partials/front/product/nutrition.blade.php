{{-- "Nutrition & repères" tab: Open Food Facts product data (by barcode) and category benchmark (by Category::off_tag). --}}
<div class="grid gap-8 lg:grid-cols-2">
    <div class="space-y-5">
        <h2 class="text-xl font-semibold">Fiche nutritionnelle</h2>
        @if ($nutrition)
            <div class="flex flex-wrap items-center gap-6">
                @if ($nutrition['nutriscore'])
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Nutri-Score</p>
                        <x-nt.nutri-score :grade="$nutrition['nutriscore']" />
                    </div>
                @endif
                @if ($nutrition['nova'])
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Transformation (NOVA)</p>
                        <x-nt.badge :variant="$nutrition['nova'] >= 4 ? 'danger' : ($nutrition['nova'] === 3 ? 'warning' : 'success')" class="px-3 py-1 text-sm">
                            {{ $nutrition['nova'] }} · {{ \App\Services\OpenFoodFacts::NOVA_LABELS[$nutrition['nova']] }}
                        </x-nt.badge>
                    </div>
                @endif
            </div>

            @if ($nutrition['nutrients'])
                <x-nt.data-table caption="Valeurs nutritionnelles pour 100 g">
                    <thead><tr><th scope="col">Pour 100 g</th><th scope="col" class="text-right">Valeur</th></tr></thead>
                    <tbody>
                        @foreach ($nutrition['nutrients'] as $nutrient)
                            <tr>
                                <td>{{ $nutrient['label'] }}</td>
                                <td class="text-right font-medium">{{ number_format($nutrient['value'], 1, ',', ' ') }} {{ $nutrient['unit'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-nt.data-table>
            @endif

            <div>
                <p class="mb-2 text-sm font-semibold">Allergènes</p>
                <div class="flex flex-wrap gap-2">
                    @forelse ($nutrition['allergens'] as $allergen)
                        <x-nt.badge variant="warning" icon="fa-triangle-exclamation">{{ $allergen }}</x-nt.badge>
                    @empty
                        <span class="text-sm text-muted-foreground">Aucun allergène réglementaire déclaré.</span>
                    @endforelse
                </div>
            </div>

            <p class="text-xs text-muted-foreground">
                Code-barres <span class="font-mono">{{ $nutrition['barcode'] }}</span> ·
                <a href="{{ $nutrition['url'] }}" target="_blank" rel="noopener" class="nt-link">Fiche Open Food Facts</a> (licence ODbL)
            </p>
        @else
            <x-nt.empty-state icon="fa-barcode" title="Pas de fiche nutritionnelle"
                              :description="$product->barcode ? 'Open Food Facts ne répond pas ou ne connaît pas encore ce code-barres.' : 'Ce produit n\'a pas de code-barres renseigné : sa fiche Open Food Facts ne peut pas être affichée.'" />
        @endif
    </div>

    <div class="space-y-5">
        <h2 class="text-xl font-semibold">Repères de la catégorie</h2>

        @if ($categoryRank)
            <div class="nt-card p-5">
                <p class="text-sm text-muted-foreground">Sur NutriTrace, catégorie « {{ $product->category->name }} »</p>
                <p class="mt-1 text-lg font-semibold">
                    Plus durable que <span class="text-primary-strong">{{ $categoryRank['better_than'] }} %</span> des produits
                </p>
                <p class="text-sm text-muted-foreground">{{ $categoryRank['rank'] }}<sup>{{ $categoryRank['rank'] === 1 ? 'er' : 'e' }}</sup> sur {{ $categoryRank['count'] }} · éco-score moyen de la catégorie : {{ $categoryRank['average_grade'] }} ({{ $categoryRank['average_points'] }} pts)</p>
                <x-nt.grade-bar :distribution="$categoryGrades" label="Éco-scores NutriTrace" class="mt-4" />
            </div>
        @endif

        @if ($offBenchmark)
            <div class="nt-card space-y-4 p-5">
                <p class="text-sm text-muted-foreground">
                    Dans le monde, {{ number_format($offBenchmark['total'], 0, ',', ' ') }} produits référencés par Open Food Facts
                    (<a href="{{ $offBenchmark['url'] }}" target="_blank" rel="noopener" class="nt-link font-mono text-xs">{{ $product->category->off_tag }}</a>)
                </p>
                <dl class="grid gap-4 sm:grid-cols-2">
                    @foreach (['green_score' => 'Green-Score moyen', 'nutriscore' => 'Nutri-Score moyen'] as $key => $label)
                        @if ($offBenchmark[$key.'_avg'])
                            <div class="space-y-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $label }}</dt>
                                <dd><x-nt.nutri-score :grade="$offBenchmark[$key.'_avg']" :label="$label" /></dd>
                                <x-nt.grade-bar :distribution="$offBenchmark[$key]" :label="$label" />
                            </div>
                        @endif
                    @endforeach
                </dl>
                @if ($product->eco_score && $offBenchmark['green_score_avg'])
                    @php $diff = ord($offBenchmark['green_score_avg']) - ord($product->eco_score); @endphp
                    <p @class(['rounded-lg px-4 py-3 text-sm', 'bg-primary/10 text-primary-strong' => $diff > 0, 'bg-muted' => $diff === 0, 'bg-danger/10 text-danger-strong' => $diff < 0])>
                        @if ($diff > 0)
                            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Éco-score {{ $product->eco_score }} : mieux que la moyenne mondiale de sa catégorie ({{ $offBenchmark['green_score_avg'] }}).
                        @elseif ($diff === 0)
                            <i class="fa-solid fa-equals" aria-hidden="true"></i> Éco-score {{ $product->eco_score }} : dans la moyenne mondiale de sa catégorie.
                        @else
                            <i class="fa-solid fa-arrow-trend-down" aria-hidden="true"></i> Éco-score {{ $product->eco_score }} : en dessous de la moyenne mondiale de sa catégorie ({{ $offBenchmark['green_score_avg'] }}).
                        @endif
                    </p>
                    <p class="text-xs text-muted-foreground">Indicatif : l'éco-score NutriTrace et le Green-Score d'Open Food Facts n'utilisent pas exactement la même méthode.</p>
                @endif
            </div>
        @endif

        @if (! $categoryRank && ! $offBenchmark)
            <x-nt.empty-state icon="fa-chart-simple" title="Pas encore de repères" description="Il faut au moins deux produits évalués dans la catégorie, ou un tag Open Food Facts sur la catégorie." />
        @endif
    </div>
</div>
