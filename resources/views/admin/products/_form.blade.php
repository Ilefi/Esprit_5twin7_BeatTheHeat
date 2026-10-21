{{-- Shared by create & edit: @include('admin.products._form', ['product' => $product ?? null]) --}}
<form method="POST" action="{{ $product ? route('admin.products.update', $product->id) : route('admin.products.store') }}" enctype="multipart/form-data" novalidate class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <div class="space-y-6">
        <x-nt.card x-data="ntBarcodeImport('{{ route('admin.products.lookup', '00000000') }}', {{ Js::from(old('barcode', $product?->barcode) ?? '') }})">
            <x-slot:header>
                <h2 class="text-base font-semibold">Import Open Food Facts</h2>
                <x-nt.badge variant="info" icon="fa-barcode">Base ouverte, sans clé</x-nt.badge>
            </x-slot:header>
            <div class="space-y-4">
                <div class="flex flex-wrap items-start gap-3">
                    <x-nt.form.input name="barcode" label="Code-barres (EAN)" :value="$product?->barcode" inputmode="numeric" placeholder="3017620422003"
                                     class="min-w-0 flex-1" x-model.trim="barcode" x-on:keydown.enter.prevent="lookup()" />
                    <x-nt.button variant="outline" icon="fa-cloud-arrow-down" x-on:click="lookup()" x-bind:disabled="! valid || loading" class="mt-7">
                        <span x-text="loading ? 'Recherche…' : 'Importer'">Importer</span>
                    </x-nt.button>
                </div>
                <p class="text-xs text-muted-foreground">Facultatif. Remplit les champs vides depuis Open Food Facts et affiche la nutrition sur la page publique du produit.</p>

                <div x-show="error" x-cloak class="flex items-center gap-2 rounded-lg bg-danger/10 px-4 py-3 text-sm text-danger-strong" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span x-text="error"></span>
                </div>

                <div x-show="result" x-cloak class="space-y-2 rounded-lg bg-primary/8 px-4 py-3 text-sm" role="status">
                    <p class="font-semibold text-primary-strong"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span x-text="result?.name ?? 'Produit trouvé'"></span> <span class="font-normal text-muted-foreground" x-text="result?.brands ? '· ' + result.brands : ''"></span></p>
                    <p class="text-muted-foreground">
                        <span x-text="filled.length ? filled.length + ' champ(s) vide(s) rempli(s).' : 'Aucun champ vide à remplir : vos saisies sont conservées.'"></span>
                        <span x-show="result?.category" x-text="'Catégorie suggérée : ' + result?.category + '.'"></span>
                    </p>
                    <p class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                        <span x-show="result?.nutriscore" x-text="'Nutri-Score ' + result?.nutriscore"></span>
                        <span x-show="result?.nova" x-text="result?.nova"></span>
                        <span x-show="result?.allergens?.length" x-text="'Allergènes : ' + result?.allergens?.join(', ')"></span>
                        <a x-bind:href="result?.url" target="_blank" rel="noopener" class="nt-link">Voir sur Open Food Facts</a>
                    </p>
                </div>
            </div>
        </x-nt.card>

        <x-nt.card x-data="ntAiProductWriter('{{ route('admin.products.ai') }}')">
            <x-slot:header>
                <h2 class="text-base font-semibold">Informations générales</h2>
                <x-nt.button variant="gold" size="sm" icon="fa-wand-magic-sparkles" x-on:click="generate()" x-bind:disabled="loading">
                    <span x-text="loading ? 'Rédaction…' : 'Rédiger avec l’IA'">Rédiger avec l’IA</span>
                </x-nt.button>
            </x-slot:header>
            <p class="mb-5 text-xs text-muted-foreground">
                <i class="fa-solid fa-wand-magic-sparkles text-gold-strong" aria-hidden="true"></i>
                Saisissez le nom (et si possible la catégorie, la région, le format, les certifications ou le code-barres) : Gemini rédige la description et la composition des champs vides.
            </p>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-nt.form.input name="name" label="Nom du produit" :value="$product?->name" required class="sm:col-span-2" />
                <x-nt.form.select name="category_id" label="Catégorie" :options="$categories" :value="$product?->category->id" placeholder="Choisir…" required />
                <x-nt.form.select name="producer_id" label="Producteur" :options="$producers" :value="$product?->producer->id" placeholder="Choisir…" required />
                <x-nt.form.input name="region" label="Région d'origine" :value="$product?->region" required />
                <x-nt.form.input name="format" label="Format" :value="$product?->format" placeholder="Bouteille verre 75 cl" required />
                <x-nt.form.textarea name="description" label="Description" :value="$product?->description" rows="4" :maxlength="2000" required class="sm:col-span-2" />
                <x-nt.form.textarea name="composition" label="Composition" :value="$product?->composition" rows="2" :maxlength="1000" class="sm:col-span-2" />
            </div>

            <div x-show="error" x-cloak class="mt-5 flex items-center gap-2 rounded-lg bg-danger/10 px-4 py-3 text-sm text-danger-strong" role="alert">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span x-text="error"></span>
            </div>

            <div x-show="result" x-cloak class="mt-5 space-y-3 rounded-lg bg-gold/15 px-4 py-3 text-sm" role="status">
                <p class="font-semibold text-gold-strong"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Brouillon rédigé par Gemini</p>
                <p class="text-muted-foreground" x-text="filled.length ? 'Rempli : ' + filled.join(' et ') + '. Relisez et corrigez avant d’enregistrer.' : 'Vos champs étaient déjà remplis : rien n’a été remplacé. Voici la proposition :'"></p>
                <template x-if="result && ! filled.includes('description')">
                    <blockquote class="border-l-2 border-gold pl-3 text-foreground" x-text="result.description"></blockquote>
                </template>
                <p x-show="result?.warnings?.length" class="text-warning-strong">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <span x-text="'Allégation à justifier ou à retirer : « ' + result?.warnings?.join(' », « ') + ' ».'"></span>
                </p>
                <x-nt.button variant="outline" size="sm" icon="fa-arrows-rotate" x-show="result && ! filled.includes('description')" x-on:click="applyAll()">Remplacer mes textes par la proposition</x-nt.button>
            </div>
        </x-nt.card>

        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Certifications</h2></x-slot:header>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($certifications as $certification)
                    <x-nt.form.checkbox name="certifications[]" :value="$certification->id" :label="$certification->name" :description="$certification->issuer"
                                        :checked="$product?->certifications->contains('id', $certification->id) ?? false" />
                @endforeach
            </div>
        </x-nt.card>
    </div>

    <div class="space-y-6">
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Publication</h2></x-slot:header>
            <div class="space-y-5">
                <x-nt.form.select name="status" label="Statut" :options="$statuses" :value="$product?->status ?? 'published'" required />
                <x-nt.form.input name="price" type="number" step="0.01" min="0" label="Prix (DT)" :value="$product?->price" required />
            </div>
            <x-slot:footer>
                <x-nt.button :href="$product ? route('admin.products.show', $product->id) : route('admin.products.index')" variant="ghost">Annuler</x-nt.button>
                <x-nt.button type="submit" icon="fa-check">{{ $product ? 'Enregistrer' : 'Créer le produit' }}</x-nt.button>
            </x-slot:footer>
        </x-nt.card>

        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Visuel</h2></x-slot:header>
            @if ($product)
                <x-nt.product-image :product="$product" size="md" class="mb-4 aspect-[4/3] rounded-lg" />
            @endif
            <x-nt.form.file name="image" accept=".jpg,.jpeg,.png,.webp" :required="! $product"
                            :hint="'JPG, PNG ou WebP — 4 Mo maximum, de 400 × 300 à 4000 × 4000 px.'.($product ? ' Laissez vide pour garder l\'image actuelle.' : '')" />
        </x-nt.card>
    </div>
</form>
