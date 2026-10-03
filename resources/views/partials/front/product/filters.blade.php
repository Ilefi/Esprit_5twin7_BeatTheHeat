{{-- Catalogue filters (sidebar on desktop, off-canvas on mobile). Uses @checked / @selected. --}}
<form method="GET" action="{{ route('front.products.index') }}" class="space-y-6" id="{{ $prefix }}-catalogue-filters">
    @if (request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif
    <input type="hidden" name="vue" value="{{ $layout === 'list' ? 'liste' : 'grille' }}">

    <fieldset>
        <legend class="nt-label mb-3">Catégorie</legend>
        <div class="space-y-2">
            <label class="flex cursor-pointer items-center gap-3 text-sm">
                <input type="radio" name="categorie" value="" class="nt-checkbox" @checked(! request('categorie'))> Toutes
            </label>
            @foreach ($categories as $category)
                <label class="flex cursor-pointer items-center gap-3 text-sm">
                    <input type="radio" name="categorie" value="{{ $category->slug }}" class="nt-checkbox" @checked(request('categorie') === $category->slug)>
                    <i class="fa-solid {{ $category->icon }} w-4 text-center text-muted-foreground" aria-hidden="true"></i>
                    <span class="flex-1">{{ $category->name }}</span>
                    <span class="text-xs text-muted-foreground">{{ $category->products_count }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset class="border-t pt-6">
        <legend class="nt-label mb-3">Éco-score</legend>
        <div class="flex flex-wrap gap-2">
            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                <label class="cursor-pointer">
                    <input type="checkbox" name="eco[]" value="{{ $grade }}" class="peer sr-only" @checked(in_array($grade, (array) request('eco', []), true))>
                    <span @class([
                        'grid h-10 w-10 place-items-center rounded-lg border-2 border-transparent font-heading font-bold transition peer-checked:border-foreground peer-focus-visible:ring-2 peer-focus-visible:ring-ring',
                        'bg-eco-a text-primary-foreground' => $grade === 'A',
                        'bg-eco-b text-foreground' => $grade === 'B',
                        'bg-eco-c text-foreground' => $grade === 'C',
                        'bg-eco-d text-foreground' => $grade === 'D',
                        'bg-eco-e text-danger-foreground' => $grade === 'E',
                    ])>{{ $grade }}</span>
                    <span class="sr-only">Éco-score {{ $grade }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset class="border-t pt-6">
        <legend class="nt-label mb-3">Certification</legend>
        <div class="space-y-2">
            @foreach ($certifications as $certification)
                <label class="flex cursor-pointer items-center gap-3 text-sm">
                    <input type="radio" name="certification" value="{{ $certification->slug }}" class="nt-checkbox" @checked(request('certification') === $certification->slug)>
                    <x-nt.cert-badge :certification="$certification" />
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="space-y-4 border-t pt-6">
        <div>
            <label for="{{ $prefix }}-filter-region" class="nt-label">Région</label>
            <select id="{{ $prefix }}-filter-region" name="region" class="nt-input">
                <option value="">Toutes les régions</option>
                @foreach ($regions as $region)
                    <option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="{{ $prefix }}-filter-sort" class="nt-label">Trier par</label>
            <select id="{{ $prefix }}-filter-sort" name="tri" class="nt-input">
                @foreach ($sorts as $value => $label)
                    <option value="{{ $value }}" @selected(request('tri', 'popular') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex gap-2 border-t pt-6">
        <x-nt.button type="submit" icon="fa-filter" class="flex-1">Appliquer</x-nt.button>
        <x-nt.button :href="route('front.products.index')" variant="ghost" aria-label="Réinitialiser les filtres" icon="fa-rotate-left" />
    </div>
</form>
