{{-- Open Food Facts tag + substitute categories, shared by the create & edit modals (expects $bag, $useOld, $categories). --}}
@php $prefix = $current ? 'category-'.$current->id : 'new-category'; @endphp

<x-nt.form.input name="off_tag" label="Tag Open Food Facts" :value="$current?->off_tag" :bag="$bag" :use-old="$useOld" :id="$prefix.'-off-tag'"
                 placeholder="en:honeys" hint="Catégorie de la taxonomie Open Food Facts, utilisée pour comparer nos produits à la moyenne mondiale." />

<fieldset class="space-y-2">
    <legend class="nt-label">Catégories de remplacement</legend>
    <p class="text-xs text-muted-foreground">Leurs produits sont proposés comme « meilleures alternatives » (le lien vaut dans les deux sens).</p>
    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($categories->where('id', '!=', $current?->id) as $other)
            <x-nt.form.checkbox name="substitutes[]" :value="$other->id" :label="$other->name" :id="$prefix.'-substitute-'.$other->id"
                                :bag="$bag" :use-old="$useOld" :checked="$current?->substitutes->contains('id', $other->id) ?? false" />
        @endforeach
    </div>
    @error('substitutes.*', $bag)
        <p class="flex items-center gap-1.5 text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</p>
    @enderror
</fieldset>
