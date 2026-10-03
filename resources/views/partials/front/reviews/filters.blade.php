<form method="GET" action="{{ url()->current() }}#avis" class="flex flex-wrap items-end gap-3" aria-label="Filtrer les avis">
    <div>
        <label for="review-rating" class="nt-label text-xs">Note</label>
        <select id="review-rating" name="note" class="nt-input py-2 text-sm">
            <option value="">Toutes les notes</option>
            @foreach ([5, 4, 3, 2, 1] as $star)
                <option value="{{ $star }}" @selected((int) request('note') === $star)>{{ $star }} étoile{{ $star > 1 ? 's' : '' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="review-sort" class="nt-label text-xs">Trier</label>
        <select id="review-sort" name="tri_avis" class="nt-input py-2 text-sm">
            @foreach (['recent' => 'Plus récents', 'useful' => 'Plus utiles', 'rating_desc' => 'Meilleures notes', 'rating_asc' => 'Notes les plus basses'] as $value => $label)
                <option value="{{ $value }}" @selected(request('tri_avis', 'recent') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-sm">
        <input type="checkbox" name="verifie" value="1" class="nt-checkbox" @checked(request()->boolean('verifie'))> Achats vérifiés
    </label>
    <x-nt.button type="submit" variant="outline" size="sm" class="mb-0.5" icon="fa-filter">Filtrer</x-nt.button>
    @if (request()->hasAny(['note', 'tri_avis', 'verifie']))
        <a href="{{ url()->current() }}#avis" class="nt-link mb-2 text-sm">Réinitialiser</a>
    @endif
</form>
