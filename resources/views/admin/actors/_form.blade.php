{{-- Shared by create & edit: @include('admin.actors._form', ['actor' => $actor ?? null]) --}}
<form method="POST" action="{{ $actor ? route('admin.actors.update', $actor->id) : route('admin.actors.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    @csrf
    @if ($actor)
        @method('PUT')
    @endif

    <x-nt.card>
        <x-slot:header><h2 class="text-base font-semibold">Identité de l'acteur</h2></x-slot:header>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-nt.form.input name="name" label="Raison sociale" :value="$actor?->name" required class="sm:col-span-2" />
            <x-nt.form.select name="type" label="Type" :options="$types" :value="$actor?->type" placeholder="Choisir…" required />
            <x-nt.form.input name="founded_year" type="number" label="Année de création" :value="$actor?->founded_year" min="1900" :max="date('Y')" />
            <x-nt.form.input name="city" label="Ville" :value="$actor?->city" required />
            <x-nt.form.input name="region" label="Gouvernorat" :value="$actor?->region" required />
            <x-nt.form.input name="email" type="email" label="E-mail de contact" :value="$actor?->email" required class="sm:col-span-2" />
            <x-nt.form.textarea name="description" label="Présentation" :value="$actor?->description" rows="4" :maxlength="2000" required class="sm:col-span-2" />
        </div>
    </x-nt.card>

    <div class="space-y-6">
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Certifications détenues</h2></x-slot:header>
            <div class="space-y-3">
                @foreach ($certifications as $certification)
                    <x-nt.form.checkbox name="certifications[]" :value="$certification->id" :label="$certification->name"
                                        :checked="$actor?->certifications->contains('id', $certification->id) ?? false" />
                @endforeach
            </div>
            <x-slot:footer>
                <x-nt.button :href="route('admin.actors.index')" variant="ghost">Annuler</x-nt.button>
                <x-nt.button type="submit" icon="fa-check">{{ $actor ? 'Enregistrer' : 'Créer l\'acteur' }}</x-nt.button>
            </x-slot:footer>
        </x-nt.card>
    </div>
</form>
