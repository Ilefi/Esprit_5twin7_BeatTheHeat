@php
    $factor = $factor ?? null;
@endphp

<form method="POST" action="{{ $factor ? route('admin.factors.update', $factor->id) : route('admin.factors.store') }}"
      novalidate class="max-w-2xl space-y-6">
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
            <x-nt.form.input
                name="name"
                label="Nom du facteur"
                :value="old('name', $factor?->name)"
                placeholder="Ex. Transport routier (camion 19 t), Emballage carton..."
                icon="fa-tag"
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-nt.form.select
                    name="category"
                    label="Catégorie"
                    :options="$categories"
                    :value="old('category', $factor?->category)"
                    placeholder="Choisir une catégorie…"
                />

                <x-nt.form.input
                    name="unit"
                    label="Unité de référence"
                    :value="old('unit', $factor?->unit)"
                    placeholder="kg CO₂e / kg, L / kg, kWh..."
                    icon="fa-scale-balanced"
                />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-nt.form.input
                    name="value"
                    type="text"
                    label="Valeur de référence"
                    :value="old('value', $factor?->value)"
                    placeholder="0.096"
                    icon="fa-calculator"
                />

                <x-nt.form.input
                    name="source"
                    label="Source scientifique / officielle"
                    :value="old('source', $factor?->source)"
                    placeholder="ADEME Base Empreinte, Agribalyse 3.1..."
                    icon="fa-book-bookmark"
                />
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
