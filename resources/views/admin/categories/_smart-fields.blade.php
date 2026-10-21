{{-- AI suggestions (Gemini), Open Food Facts tag + substitute categories, shared by the create & edit modals (expects $current, $bag, $useOld, $categories). --}}
@php
    $prefix = $current ? 'category-'.$current->id : 'new-category';
    $formId = $current ? 'edit-category-form-'.$current->id : 'create-category-form';
@endphp

<div x-data="ntAiCategoryAssistant('{{ route('admin.categories.ai') }}', '{{ $formId }}', {{ $current?->id ?? 'null' }})" class="space-y-3 rounded-lg border border-dashed p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground"><i class="fa-solid fa-wand-magic-sparkles text-gold-strong" aria-hidden="true"></i> Gemini propose l'icône, le tag et les catégories de remplacement à partir du nom.</p>
        <x-nt.button variant="gold" size="sm" icon="fa-wand-magic-sparkles" x-on:click="generate()" x-bind:disabled="loading">
            <span x-text="loading ? 'Analyse…' : 'Suggérer avec l’IA'">Suggérer avec l’IA</span>
        </x-nt.button>
    </div>

    <div x-show="error" x-cloak class="flex items-center gap-2 rounded-lg bg-danger/10 px-4 py-3 text-sm text-danger-strong" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span x-text="error"></span>
    </div>

    <template x-if="result">
        <div class="space-y-2 rounded-lg bg-gold/15 px-4 py-3 text-sm" role="status">
            <p class="font-semibold text-gold-strong" x-text="result.summary ?? 'Suggestions de Gemini'"></p>
            <ul class="space-y-1.5">
                <li x-show="result.icon"><span class="text-muted-foreground">Icône :</span> <i class="fa-solid" x-bind:class="result.icon" aria-hidden="true"></i></li>
                <li x-show="result.off_tag">
                    <span class="text-muted-foreground">Tag Open Food Facts :</span> <span class="font-mono text-xs" x-text="result.off_tag"></span>
                    <span x-show="result.off_products" class="text-success-strong" x-text="'· vérifié, ' + result.off_products?.toLocaleString('fr-FR') + ' produits'"></span>
                    <span x-show="! result.off_products" class="text-warning-strong">· introuvable sur Open Food Facts, à vérifier</span>
                </li>
                <template x-for="substitute in result.substitutes" x-bind:key="substitute.id">
                    <li><span class="text-muted-foreground">Remplaçable par</span> <strong x-text="substitute.name"></strong> <span class="text-muted-foreground" x-text="'— ' + substitute.reason"></span></li>
                </template>
                <li x-show="! result.substitutes.length" class="text-muted-foreground">Aucune catégorie de remplacement proposée.</li>
            </ul>
            <p class="text-xs text-muted-foreground" x-text="applied ? 'Suggestions appliquées : vérifiez puis enregistrez.' : 'Champs vides remplis et remplacements cochés. Rien n’est enregistré avant votre validation.'"></p>
            <x-nt.button variant="outline" size="sm" icon="fa-arrows-rotate" x-show="! applied" x-on:click="apply()">Appliquer toutes les suggestions</x-nt.button>
        </div>
    </template>
</div>

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
