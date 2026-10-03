{{--
    Data-driven filter bar for admin lists.
    @include('partials.admin.filters', [
        'action' => route('admin.products.index'),
        'search' => 'Rechercher un produit…',               // optional, field "q"
        'selects' => ['statut' => ['Statut', $statuses]],   // name => [label, options]
        'keep' => ['vue'],                                  // optional query keys to preserve
    ])
--}}
<x-nt.filter-bar :action="$action">
    @foreach ($keep ?? [] as $key)
        @if (request()->filled($key))
            <input type="hidden" name="{{ $key }}" value="{{ request($key) }}">
        @endif
    @endforeach

    @isset($search)
        <x-nt.form.input name="q" label="Recherche" :value="request('q')" :use-old="false" icon="fa-magnifying-glass" :placeholder="$search" type="search" class="sm:min-w-64 sm:flex-1" />
    @endisset

    @foreach ($selects ?? [] as $name => [$label, $options])
        <x-nt.form.select :name="$name" :label="$label" :options="$options" placeholder="Tous" :value="request($name)" :id="'filter-'.$name" class="sm:min-w-40" />
    @endforeach
</x-nt.filter-bar>
