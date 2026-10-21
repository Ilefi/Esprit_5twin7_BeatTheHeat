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
      novalidate
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
                <x-nt.form.select name="product_id" label="Produit" :options="$products" :value="request('produit')" placeholder="Choisir…" class="sm:col-span-2"
                                  :hint="$products ? 'Seuls les produits sans empreinte sont proposés.' : 'Tous les produits ont déjà une empreinte.'" />
            @endif
            <x-nt.form.input name="co2_per_kg" type="text" label="Émissions (kg CO₂e / kg)" :value="$initial['co2']" x-model.number="co2" icon="fa-smog" />
            <x-nt.form.input name="water_per_kg" type="text" label="Eau (L / kg)" :value="$initial['water']" x-model.number="water" icon="fa-droplet" />
            <x-nt.form.input name="distance_km" type="text" label="Distance parcourue (km)" :value="$initial['distance']" x-model.number="distance" icon="fa-route" />
            <x-nt.form.select name="packaging" label="Emballage" :options="$packaging" :value="$initial['packaging']" x-model="packaging" />
            <div class="sm:col-span-2">
                <input type="hidden" name="seasonal" value="0">
                <x-nt.form.checkbox name="seasonal" label="Produit de saison" description="Récolté et vendu en pleine saison, sans stockage prolongé." :checked="$initial['seasonal']" x-model="seasonal" />
            </div>
            <x-nt.form.select name="emission_factor_id" label="Facteur d'émission de référence" :options="$emissionFactors" :value="old('emission_factor_id', $impact?->emission_factor_id)" placeholder="Associer un facteur de calcul…" hint="Jointure avec la base des facteurs d'émission pour traçabilité du calcul." />
            <x-nt.form.select name="methodology" label="Méthodologie" :options="$methodologies" :value="$impact?->methodology" placeholder="Choisir…" />
            <x-nt.form.input name="source" label="Source des données" :value="$impact?->source" maxlength="255" placeholder="Rapport ACV 2026, bilan carbone…" class="sm:col-span-2" />
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
                    <x-nt.form.input :name="'breakdown['.$stage.']'" type="text" :label="$stage" :value="$share" x-model.number="parts.{{ $stage }}" />
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

    <div class="space-y-6 xl:sticky xl:top-24 xl:self-start">
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

        <!-- Module IA : Optimiseur d'empreinte environnementale -->
        <x-nt.card x-data="{
            loading: false,
            result: null,
            error: null,
            async analyze() {
                this.loading = true;
                this.error = null;
                try {
                    const factorSelect = document.querySelector('select[name=emission_factor_id]');
                    const res = await fetch('{{ route('admin.impacts.ai.optimize') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            co2_per_kg: this.co2,
                            water_per_kg: this.water,
                            distance_km: this.distance,
                            packaging: this.packaging,
                            seasonal: this.seasonal,
                            emission_factor_id: factorSelect ? factorSelect.value : null
                        })
                    });
                    if (!res.ok) throw new Error('Erreur lors de l’analyse IA');
                    this.result = await res.json();
                } catch (e) {
                    this.error = 'Impossible de générer l’optimisation IA pour le moment.';
                } finally {
                    this.loading = false;
                }
            }
        }">
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-primary-strong" aria-hidden="true"></i>
                        <h2 class="text-base font-semibold">Conseils IA Éco-conception</h2>
                    </div>
                    <span class="nt-badge bg-primary/10 text-primary-strong text-xs font-semibold">IA Gemini / ACV</span>
                </div>
            </x-slot:header>

            <div class="space-y-4">
                <p class="text-xs text-muted-foreground">
                    L'intelligence artificielle analyse vos paramètres d'émissions et facteurs associés pour vous proposer des leviers immédiats d'amélioration de l'éco-score.
                </p>

                <button type="button"
                        x-on:click="analyze()"
                        :disabled="loading"
                        class="nt-btn nt-btn-outline w-full flex items-center justify-center gap-2 text-sm font-medium">
                    <template x-if="loading">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Analyse ACV en cours...
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Générer les pistes d'amélioration
                        </span>
                    </template>
                </button>

                <template x-if="error">
                    <p class="text-xs text-danger-strong flex items-center gap-1.5" x-text="error"></p>
                </template>

                <template x-if="result">
                    <div class="mt-4 space-y-3 rounded-lg bg-surface-alt p-3.5 text-xs border border-border">
                        <div class="flex items-center justify-between font-semibold text-foreground">
                            <span>Diagnostic global :</span>
                            <span class="nt-badge nt-badge-success text-[11px]" x-text="result.potential_gain"></span>
                        </div>
                        <p class="text-muted-foreground italic leading-relaxed" x-text="result.summary"></p>

                        <div class="pt-2 border-t border-border space-y-2">
                            <p class="font-semibold text-foreground">Pistes d'actions concrètes :</p>
                            <template x-for="(rec, idx) in result.recommendations" :key="idx">
                                <div class="rounded bg-surface p-2 border border-border/60">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-primary-strong text-[11px]" x-text="rec.stage"></span>
                                        <span class="nt-badge text-[10px] bg-primary/10 text-primary-strong font-mono" x-text="rec.saving"></span>
                                    </div>
                                    <p class="text-foreground/90 text-[11px]" x-text="rec.action"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </x-nt.card>
    </div>
</form>
