{{-- Shared by create & edit: @include('admin.products._form', ['product' => $product ?? null]) --}}
<form method="POST" action="{{ $product ? route('admin.products.update', $product->id) : route('admin.products.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <div class="space-y-6">
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Informations générales</h2></x-slot:header>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-nt.form.input name="name" label="Nom du produit" :value="$product?->name" required class="sm:col-span-2" />
                <x-nt.form.select name="category_id" label="Catégorie" :options="$categories" :value="$product?->category->id" placeholder="Choisir…" required />
                <x-nt.form.select name="producer_id" label="Producteur" :options="$producers" :value="$product?->producer->id" placeholder="Choisir…" required />
                <x-nt.form.input name="region" label="Région d'origine" :value="$product?->region" required />
                <x-nt.form.input name="format" label="Format" :value="$product?->format" placeholder="Bouteille verre 75 cl" required />
                <x-nt.form.textarea name="description" label="Description" :value="$product?->description" rows="4" :maxlength="2000" required class="sm:col-span-2" />
                <x-nt.form.textarea name="composition" label="Composition" :value="$product?->composition" rows="2" :maxlength="1000" class="sm:col-span-2" />
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
                <x-nt.form.select name="status" label="Statut" :options="$statuses" :value="$product?->status ?? 'draft'" required />
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
            <x-nt.form.file name="image" accept=".jpg,.jpeg,.png,.webp" hint="JPG, PNG ou WebP — 4 Mo maximum." />
        </x-nt.card>
    </div>
</form>
