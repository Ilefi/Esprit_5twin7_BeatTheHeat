@php
    $factor = $factor ?? null;
@endphp

<div x-data="{
    name: @js(old('name', $factor?->name ?? '')),
    category: @js(old('category', $factor?->category ?? '')),
    unit: @js(old('unit', $factor?->unit ?? '')),
    value: @js(old('value', $factor?->value ?? '')),
    source: @js(old('source', $factor?->source ?? '')),
    loading: false,
    explanation: null,
    error: null,
    async estimateWithAi() {
        if (!this.name || this.name.trim().length < 2) {
            this.error = 'Veuillez saisir un nom de facteur avant de lancer l\'estimation IA.';
            return;
        }
        this.loading = true;
        this.error = null;
        this.explanation = null;

        try {
            const response = await fetch('{{ route('admin.factors.ai.estimate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: this.name,
                    category: this.category || null
                })
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la communication avec l\'assistant IA.');
            }

            const data = await response.json();
            if (data.category) this.category = data.category;
            if (data.unit) this.unit = data.unit;
            if (data.value !== undefined) this.value = data.value;
            if (data.source) this.source = data.source;
            if (data.explanation) this.explanation = data.explanation;
        } catch (e) {
            this.error = e.message || 'Impossible d\'estimer le facteur.';
        } finally {
            this.loading = false;
        }
    }
}" class="max-w-3xl space-y-6">

    <!-- AI Helper Card -->
    <div class="rounded-xl border border-primary/20 bg-gradient-to-r from-primary/5 via-primary/10 to-primary/5 p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                </span>
                <div>
                    <h3 class="font-heading font-semibold text-foreground">Assistant IA - Éco-Facteurs</h3>
                    <p class="text-xs text-muted-foreground">
                        Saisissez le nom du facteur puis laissez l'IA estimer la valeur carbone, l'unité et la source officielle (ADEME / Agribalyse).
                    </p>
                </div>
            </div>
            <button type="button"
                    x-on:click="estimateWithAi()"
                    :disabled="loading"
                    class="nt-btn nt-btn-primary nt-btn-sm shrink-0 shadow-sm transition hover:scale-105 active:scale-95 disabled:opacity-50">
                <template x-if="loading">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                        <span>Estimation en cours...</span>
                    </span>
                </template>
                <template x-if="!loading">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                        <span>Estimer avec l'IA</span>
                    </span>
                </template>
            </button>
        </div>

        <template x-if="error">
            <div class="mt-3 flex items-center gap-2 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-xs text-danger-strong">
                <i class="fa-solid fa-triangle-exclamation shrink-0"></i>
                <span x-text="error"></span>
            </div>
        </template>

        <template x-if="explanation">
            <div class="mt-3 rounded-lg border border-primary/30 bg-background/80 p-3 text-xs shadow-xs" x-transition>
                <div class="flex items-center gap-1.5 font-semibold text-primary">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Estimation IA appliquée avec succès</span>
                </div>
                <p class="mt-1 text-muted-foreground" x-text="explanation"></p>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ $factor ? route('admin.factors.update', $factor->id) : route('admin.factors.store') }}"
          novalidate class="space-y-6">
        @csrf
        @if ($factor)
            @method('PUT')
        @endif

        <x-nt.card>
            <x-slot:header>
                <h2 class="text-base font-semibold">
                    {{ $factor ? 'Modifier le facteur d\'émission' : 'Nouveau facteur d\'émission' }}
                </h2>
            </x-slot:header>

            <div class="space-y-4">
                <div>
                    <label for="name" class="nt-label mb-1 block">Nom du facteur <span class="text-danger-strong">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                            <i class="fa-solid fa-tag" aria-hidden="true"></i>
                        </span>
                        <input id="name"
                               name="name"
                               type="text"
                               required
                               x-model="name"
                               class="nt-input pl-10"
                               placeholder="Ex. Transport routier (camion 19 t), Emballage brique carton..." />
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-xs text-danger-strong">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="category" class="nt-label mb-1 block">Catégorie <span class="text-danger-strong">*</span></label>
                        <select id="category"
                                name="category"
                                required
                                x-model="category"
                                class="nt-input">
                            <option value="">Choisir une catégorie…</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1.5 text-xs text-danger-strong">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="nt-label mb-1 block">Unité de référence <span class="text-danger-strong">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                                <i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>
                            </span>
                            <input id="unit"
                                   name="unit"
                                   type="text"
                                   required
                                   x-model="unit"
                                   class="nt-input pl-10"
                                   placeholder="kg CO₂e / kg, L / kg, kWh..." />
                        </div>
                        @error('unit')
                            <p class="mt-1.5 text-xs text-danger-strong">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="value" class="nt-label mb-1 block">Valeur de référence <span class="text-danger-strong">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                                <i class="fa-solid fa-calculator" aria-hidden="true"></i>
                            </span>
                            <input id="value"
                                   name="value"
                                   type="text"
                                   required
                                   x-model="value"
                                   class="nt-input pl-10"
                                   placeholder="0.096" />
                        </div>
                        @error('value')
                            <p class="mt-1.5 text-xs text-danger-strong">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="source" class="nt-label mb-1 block">Source scientifique / officielle</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                                <i class="fa-solid fa-book-bookmark" aria-hidden="true"></i>
                            </span>
                            <input id="source"
                                   name="source"
                                   type="text"
                                   x-model="source"
                                   class="nt-input pl-10"
                                   placeholder="ADEME Base Empreinte, Agribalyse 3.1..." />
                        </div>
                        @error('source')
                            <p class="mt-1.5 text-xs text-danger-strong">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between">
                    <x-nt.button :href="route('admin.factors.index')" variant="ghost" size="sm">
                        Annuler
                    </x-nt.button>
                    <x-nt.button type="submit" size="sm" icon="fa-check">
                        {{ $factor ? 'Enregistrer les modifications' : 'Créer le facteur' }}
                    </x-nt.button>
                </div>
            </x-slot:footer>
        </x-nt.card>
    </form>
</div>
