{{-- Shared by create & edit: @include('admin.impacts._form', ['product' => $product ?? null]) — live eco-score preview via Alpine (ntEcoPreview). --}}
@php
    $impact = $product?->impact;
    $initial = [
        'co2' => (float) old('co2_per_kg', $impact?->co2_per_kg ?? 1.5),
        'water' => (float) old('water_per_kg', $impact?->water_per_kg ?? 800),
        'distance' => (float) old('distance_km', $impact?->distance_km ?? 150),
        'packaging' => old('packaging', $impact?->packaging ?? 'recyclable'),
        'seasonal' => (bool) old('seasonal', $impact?->seasonal ?? true),
    ];
    $breakdown = array_map('intval', old('breakdown', $impact?->breakdown ?? array_combine(\App\Support\EcoScore::BREAKDOWN_STAGES, [60, 15, 15, 10])));
@endphp

<form method="POST" action="{{ $product ? route('admin.impacts.update', $product->id) : route('admin.impacts.store') }}"
      x-data="ntEcoPreview(@js($initial))" class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <x-nt.card>
        <x-slot:header><h2 class="text-base font-semibold">Indicateurs mesurés</h2></x-slot:header>
        <div class="grid gap-5 sm:grid-cols-2">
            @if ($product)
                <div class="sm:col-span-2">
                    <p class="nt-label">Produit</p>
                    <p class="font-semibold">{{ $product->name }}</p>
                </div>
            @else
                <x-nt.form.select name="product_id" label="Produit" :options="$products" :value="request('produit')" placeholder="Choisir…" required class="sm:col-span-2"
                                  :hint="$products ? 'Seuls les produits sans empreinte sont proposés.' : 'Tous les produits ont déjà une empreinte.'" />
            @endif
            <x-nt.form.input name="co2_per_kg" type="number" step="0.1" min="0" label="Émissions (kg CO₂e / kg)" :value="$initial['co2']" x-model.number="co2" required icon="fa-smog" />
            <x-nt.form.input name="water_per_kg" type="number" step="10" min="0" label="Eau (L / kg)" :value="$initial['water']" x-model.number="water" required icon="fa-droplet" />
            <x-nt.form.input name="distance_km" type="number" step="5" min="0" label="Distance parcourue (km)" :value="$initial['distance']" x-model.number="distance" required icon="fa-route" />
            <x-nt.form.select name="packaging" label="Emballage" :options="$packaging" :value="$initial['packaging']" x-model="packaging" required />
            <div class="sm:col-span-2">
                <input type="hidden" name="seasonal" value="0">
                <x-nt.form.checkbox name="seasonal" label="Produit de saison" description="Récolté et vendu en pleine saison, sans stockage prolongé." :checked="$initial['seasonal']" x-model="seasonal" />
            </div>
            <x-nt.form.select name="methodology" label="Méthodologie" :options="$methodologies" :value="$impact?->methodology" placeholder="Choisir…" required />
            <x-nt.form.input name="source" label="Source des données" :value="$impact?->source" maxlength="255" placeholder="Rapport ACV 2026, bilan carbone…" />
        </div>

        <fieldset class="mt-6 border-t pt-5" x-data="{ parts: @js($breakdown) }">
            <legend class="sr-only">Répartition des émissions</legend>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold">Répartition des émissions (%)</p>
                <span class="nt-badge" :class="Object.values(parts).reduce((sum, part) => sum + (Number(part) || 0), 0) === 100 ? 'nt-badge-success' : 'nt-badge-danger'">
                    Total : <span x-text="Object.values(parts).reduce((sum, part) => sum + (Number(part) || 0), 0)"></span> %
                </span>
            </div>
            <div class="grid gap-4 sm:grid-cols-4">
                @foreach ($breakdown as $stage => $share)
                    <x-nt.form.input :name="'breakdown['.$stage.']'" type="number" min="0" max="100" step="1" :label="$stage" :value="$share" x-model.number="parts.{{ $stage }}" required />
                @endforeach
            </div>
            @error('breakdown')
                <p class="mt-2 flex items-center gap-1.5 text-sm text-danger-strong">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}
                </p>
            @enderror
        </fieldset>
        <x-slot:footer>
            <x-nt.button :href="route('admin.impacts.index')" variant="ghost">Annuler</x-nt.button>
            <x-nt.button type="submit" icon="fa-check">{{ $product ? 'Enregistrer' : 'Créer l\'empreinte' }}</x-nt.button>
        </x-slot:footer>
    </x-nt.card>

    <div class="xl:sticky xl:top-24 xl:self-start">
        <x-nt.card>
            <x-slot:header>
                <h2 class="text-base font-semibold">Aperçu de l'éco-score</h2>
                <span class="nt-badge nt-badge-info">En direct</span>
            </x-slot:header>
            <div class="flex flex-col items-center gap-4 text-center" aria-live="polite">
                <div class="flex items-end gap-1.5" role="img" :aria-label="'Éco-score calculé : ' + grade">
                    @foreach (['A' => 'bg-eco-a text-primary-foreground', 'B' => 'bg-eco-b text-foreground', 'C' => 'bg-eco-c text-foreground', 'D' => 'bg-eco-d text-foreground', 'E' => 'bg-eco-e text-danger-foreground'] as $letter => $classes)
                        <span class="grid place-items-center rounded-lg font-heading font-bold transition-all duration-300 {{ $classes }}"
                              :class="grade === '{{ $letter }}' ? 'h-16 w-14 text-3xl shadow-lg' : 'h-10 w-9 text-sm opacity-30'" aria-hidden="true">{{ $letter }}</span>
                    @endforeach
                </div>
                <p class="font-heading text-4xl font-extrabold"><span x-text="points"></span><span class="text-lg text-muted-foreground"> / 100</span></p>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-muted" aria-hidden="true">
                    <div class="h-full rounded-full bg-primary transition-all duration-300" :style="`width: ${points}%`"></div>
                </div>
                <p class="text-xs text-muted-foreground">Calcul identique à celui du serveur (App\Support\EcoScore).</p>
            </div>
        </x-nt.card>
    </div>
</form>
