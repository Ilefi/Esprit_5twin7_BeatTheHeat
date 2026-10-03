{{-- Shared by create & edit: @include('admin.batches._form', ['batch' => $batch ?? null]) --}}
<x-nt.card class="max-w-3xl">
    <form method="POST" action="{{ $batch ? route('admin.batches.update', $batch->id) : route('admin.batches.store') }}" class="grid gap-5 sm:grid-cols-2">
        @csrf
        @if ($batch)
            @method('PUT')
        @endif

        <x-nt.form.input name="code" label="Code de lot" :value="$batch?->code" placeholder="NT-2026-OLV-0001" required hint="Format NT-AAAA-XXX-0000" class="font-mono" />
        <x-nt.form.select name="product_id" label="Produit" :options="$products" :value="$batch?->product->id" placeholder="Choisir…" required />
        <x-nt.form.input name="quantity" label="Quantité" :value="$batch?->quantity" placeholder="1 200 bouteilles" required />
        <x-nt.form.input name="production_date" type="date" label="Date de production" :value="$batch?->production_date->toDateString()" required />
        <x-nt.form.select name="status" label="Statut" :options="$statuses" :value="$batch?->status ?? 'in_production'" required />

        <div class="flex items-end justify-end gap-3 sm:col-span-2">
            <x-nt.button :href="$batch ? route('admin.batches.show', $batch->id) : route('admin.batches.index')" variant="ghost">Annuler</x-nt.button>
            <x-nt.button type="submit" icon="fa-check">{{ $batch ? 'Enregistrer' : 'Créer le lot' }}</x-nt.button>
        </div>
    </form>
</x-nt.card>
