@extends('layouts.admin')

@section('title', 'Modération des avis')
@section('page_title', 'Modération des avis')
@section('page_subtitle', 'Approuvez, rejetez ou examinez les avis signalés par la communauté.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Avis']]])
@endsection

@section('content')
    {{-- Status tabs with counts --}}
    <nav aria-label="Statut des avis" class="relative -mx-4 mb-6 overflow-x-auto px-4">
        <ul class="flex min-w-max gap-1 border-b">
            @foreach (['' => ['Tous', $total]] + collect($statuses)->map(fn ($label, $value) => [$label, $counts[$value] ?? 0])->all() as $value => [$label, $count])
                @php $active = (string) $status === (string) $value; @endphp
                <li>
                    <a href="{{ route('admin.reviews.index', array_filter(['statut' => $value] + request()->except(['statut', 'page']))) }}" @if ($active) aria-current="page" @endif
                       @class(['-mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition', 'border-primary text-primary-strong' => $active, 'border-transparent text-muted-foreground hover:text-foreground' => ! $active])>
                        {{ $label }} <span @class(['nt-badge px-2 py-0', 'nt-badge-danger' => $value === 'flagged' && $count, 'nt-badge-warning' => $value === 'pending' && $count])>{{ $count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    @include('partials.admin.filters', [
        'action' => route('admin.reviews.index'),
        'search' => 'Titre, contenu ou auteur…',
        'selects' => ['note' => ['Note', [5 => '5 étoiles', 4 => '4 étoiles', 3 => '3 étoiles', 2 => '2 étoiles', 1 => '1 étoile']], 'produit' => ['Produit', $products]],
        'keep' => ['statut'],
    ])

    <form method="POST" action="{{ route('admin.reviews.bulk') }}" class="mt-6" x-data="{ selected: [], ids: @js($reviews->pluck('id')) }">
        @csrf
        @error('ids')<x-nt.alert type="danger" class="mb-4">{{ $message }}</x-nt.alert>@enderror

        <x-nt.data-table caption="Avis à modérer">
            <x-slot:toolbar>
                <p class="text-sm text-muted-foreground" x-show="selected.length === 0">{{ $reviews->total() }} avis</p>
                <div class="flex flex-wrap items-center gap-2" x-show="selected.length > 0" x-cloak>
                    <span class="text-sm font-semibold" x-text="selected.length + ' sélectionné(s)'"></span>
                    <x-nt.button type="submit" name="action" value="published" size="sm" icon="fa-check">Approuver</x-nt.button>
                    <x-nt.button type="submit" name="action" value="rejected" size="sm" variant="danger" icon="fa-xmark">Rejeter</x-nt.button>
                </div>
            </x-slot:toolbar>

            <thead>
                <tr>
                    <th scope="col" class="w-10"><input type="checkbox" class="nt-checkbox" aria-label="Tout sélectionner" :checked="selected.length === ids.length && ids.length > 0" x-on:change="selected = $event.target.checked ? [...ids] : []"></th>
                    <th scope="col">Avis</th>
                    <th scope="col">Produit</th>
                    <th scope="col">Note</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Date</th>
                    <th scope="col" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr :class="selected.includes({{ $review->id }}) && 'bg-primary/6'">
                        <td><input type="checkbox" name="ids[]" value="{{ $review->id }}" class="nt-checkbox" x-model.number="selected" aria-label="Sélectionner l'avis « {{ $review->title }} »"></td>
                        <td class="max-w-sm">
                            <a href="{{ route('admin.reviews.show', $review->id) }}" class="font-semibold hover:text-primary-strong">{{ $review->title }}</a>
                            <p class="line-clamp-1 text-xs text-muted-foreground">{{ $review->user->name }} — {{ $review->body }}</p>
                        </td>
                        <td class="max-w-48 truncate">{{ $review->product->name }}</td>
                        <td><x-nt.rating-stars :rating="$review->rating" size="xs" /></td>
                        <td><x-status-badge type="review" :value="$review->status" /></td>
                        <td class="whitespace-nowrap text-xs text-muted-foreground">{{ $review->created_at->diffForHumans() }}</td>
                        <td><div class="flex justify-end"><a href="{{ route('admin.reviews.show', $review->id) }}" class="nt-btn nt-btn-outline nt-btn-sm">Examiner</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-muted-foreground">Aucun avis dans cette vue.</td></tr>
                @endforelse
            </tbody>
            <x-slot:footer>{{ $reviews->links() }}</x-slot:footer>
        </x-nt.data-table>
    </form>
@endsection
