{{-- Step form inside a modal. @include('admin.batches._step-form', ['batch' => $batch, 'step' => $step ?? null]) --}}
@php
    $bag = $step ? 'step'.$step->id : 'step';
    $failed = $errors->{$bag}->isNotEmpty();
    $prefix = $step ? 'step-'.$step->id : 'new-step';
@endphp

<x-nt.modal :name="$prefix" :title="$step ? 'Modifier l\'étape' : 'Ajouter une étape'" :show="$failed" max-width="2xl">
    <form method="POST" action="{{ $step ? route('admin.batches.steps.update', [$batch->id, $step->id]) : route('admin.batches.steps.store', $batch->id) }}"
          id="{{ $prefix }}-form" class="grid gap-5 sm:grid-cols-2">
        @csrf
        @if ($step)
            @method('PUT')
        @endif
        <x-nt.form.select name="stage" label="Maillon" :options="$stages" :value="$step?->stage" :bag="$bag" :use-old="$failed" :id="$prefix.'-stage'" placeholder="Choisir…" required />
        <x-nt.form.select name="actor_id" label="Acteur" :options="$actors" :value="$step?->actor?->id" :bag="$bag" :use-old="$failed" :id="$prefix.'-actor'" placeholder="Aucun (consommateur)" />
        <x-nt.form.input name="title" label="Intitulé" :value="$step?->title" :bag="$bag" :use-old="$failed" :id="$prefix.'-title'" required class="sm:col-span-2" />
        <x-nt.form.input name="location" label="Lieu" :value="$step?->location" :bag="$bag" :use-old="$failed" :id="$prefix.'-location'" required />
        <x-nt.form.input name="date" type="date" label="Date" :value="$step?->date->toDateString()" :bag="$bag" :use-old="$failed" :id="$prefix.'-date'" required />
        <x-nt.form.input name="distance_km" type="number" min="0" label="Distance depuis l'étape précédente (km)" :value="$step?->distance_km" :bag="$bag" :use-old="$failed" :id="$prefix.'-km'" class="sm:col-span-2" />
        <x-nt.form.textarea name="action" label="Action réalisée" :value="$step?->action" :bag="$bag" :use-old="$failed" :id="$prefix.'-action'" rows="3" :maxlength="500" required class="sm:col-span-2" />
    </form>
    <x-slot:footer>
        <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
        <x-nt.button type="submit" form="{{ $prefix }}-form" icon="fa-check">{{ $step ? 'Enregistrer' : 'Ajouter l\'étape' }}</x-nt.button>
    </x-slot:footer>
</x-nt.modal>
