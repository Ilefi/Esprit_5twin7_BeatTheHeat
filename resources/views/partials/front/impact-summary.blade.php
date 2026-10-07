{{-- Environmental footprint of one product. Reused on the product page (tab) and the comparator. --}}
@php
    $impact = $product->impact;
    $packagingLabels = \App\Support\EcoScore::PACKAGING_LABELS;
    $metrics = [
        ['fa-smog', 'Émissions', number_format($impact->co2_per_kg, 1, ',', ' '), 'kg CO₂e / kg'],
        ['fa-droplet', 'Eau', number_format($impact->water_per_kg, 0, ',', ' '), 'L / kg'],
        ['fa-route', 'Distance', number_format($impact->distance_km, 0, ',', ' '), 'km parcourus'],
        ['fa-box-open', 'Emballage', $packagingLabels[$impact->packaging], 'type'],
        ['fa-calendar-check', 'Saisonnalité', $impact->seasonal ? 'De saison' : 'Hors saison', 'au moment de la récolte'],
    ];
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
    <div>
        <div class="mb-5 flex flex-wrap items-center gap-4">
            <x-nt.eco-score :grade="$impact->eco_score" size="lg" show-label />
            <p class="text-sm text-muted-foreground"><strong class="font-heading text-2xl text-foreground">{{ $impact->eco_points }}</strong> / 100 points</p>
        </div>
        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($metrics as [$icon, $label, $value, $unit])
                <div class="rounded-lg border bg-surface p-4">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        <i class="fa-solid {{ $icon }} text-primary" aria-hidden="true"></i>{{ $label }}
                    </dt>
                    <dd class="mt-2 font-heading text-lg font-semibold leading-tight">{{ $value }}</dd>
                    <dd class="text-xs text-muted-foreground">{{ $unit }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-4 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
            <x-nt.badge variant="info" icon="fa-flask">{{ \App\Support\EcoScore::METHODOLOGIES[$impact->methodology] }}</x-nt.badge>
            @if ($impact->source)
                <span>Source : {{ $impact->source }}</span> ·
            @endif
            <span>Mis à jour {{ $impact->updated_at->diffForHumans() }}</span> ·
            <a href="{{ route('front.impact.index') }}" class="nt-link">Comment le score est calculé</a>
        </p>
    </div>

    @unless ($compact ?? false)
        <figure class="nt-card p-5">
            <figcaption class="mb-3 text-sm font-semibold">Répartition des émissions</figcaption>
            <div class="h-56">
                <canvas x-data="ntChart(@js([
                    'type' => 'doughnut',
                    'labels' => array_keys($impact->breakdown),
                    'datasets' => [['label' => 'Part des émissions (%)', 'data' => array_values($impact->breakdown), 'colors' => ['primary', 'earth', 'gold', 'info']]],
                    'unit' => '%',
                ]))" role="img" aria-label="Répartition des émissions : {{ collect($impact->breakdown)->map(fn ($v, $k) => "$k $v %")->implode(', ') }}"></canvas>
            </div>
        </figure>
    @endunless
</div>
