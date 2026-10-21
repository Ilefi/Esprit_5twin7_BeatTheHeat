{{-- Shared by create & edit: @include('admin.certifications._form', ['certification' => $certification ?? null]) --}}
<x-nt.card class="max-w-3xl">
    <form method="POST" action="{{ $certification ? route('admin.certifications.update', $certification->id) : route('admin.certifications.store') }}" class="grid gap-5 sm:grid-cols-2">
        @csrf
        @if ($certification)
            @method('PUT')
        @endif

        <x-nt.form.input name="name" label="Nom complet" :value="$certification?->name" required class="sm:col-span-2" />
        <x-nt.form.input name="short_name" label="Nom court (badge)" :value="$certification?->short_name" required maxlength="30" />
        <x-nt.form.select name="type" label="Type de label" :options="$types" :value="$certification?->type" placeholder="Choisir…" required />
        <x-nt.form.input name="issuer" label="Organisme certificateur" :value="$certification?->issuer" required />
        <x-nt.form.input name="expires_at" type="date" label="Date d'expiration" :value="$certification?->expires_at?->toDateString()" :min="$certification ? null : today()->toDateString()" hint="Laissez vide si le label n'expire pas." />
        <x-nt.form.textarea name="description" label="Description" :value="$certification?->description" rows="3" :maxlength="1500" required class="sm:col-span-2" />
        <x-nt.form.textarea name="criteria" label="Critères (un par ligne)" :value="$certification ? implode(PHP_EOL, $certification->criteria) : null" rows="4" :maxlength="2000" class="sm:col-span-2" />
        <x-nt.form.textarea name="guarantees" label="Ce qu'il garantit (un par ligne)" :value="$certification ? implode(PHP_EOL, $certification->guarantees) : null" rows="3" :maxlength="1000" />
        <x-nt.form.textarea name="limits" label="Ses limites (une par ligne)" :value="$certification ? implode(PHP_EOL, $certification->limits) : null" rows="3" :maxlength="1000" />

        <div class="flex justify-end gap-3 sm:col-span-2">
            <x-nt.button :href="route('admin.certifications.index')" variant="ghost">Annuler</x-nt.button>
            <x-nt.button type="submit" icon="fa-check">{{ $certification ? 'Enregistrer' : 'Créer la certification' }}</x-nt.button>
        </div>
    </form>
</x-nt.card>
