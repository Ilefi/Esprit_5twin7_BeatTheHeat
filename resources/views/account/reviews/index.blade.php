@extends('layouts.account')

@section('title', 'Mes avis')
@section('account_breadcrumb', 'Mes avis')
@section('account_title', 'Mes avis')
@section('account_subtitle', 'Modifiez ou supprimez vos avis. Une modification repasse en modération.')

@section('account_content')
    <nav aria-label="Filtrer par statut" class="relative -mx-4 mb-6 overflow-x-auto px-4">
        <ul class="flex min-w-max gap-2">
            <li><a href="{{ route('account.reviews.index') }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => ! request('statut'), 'nt-btn-outline' => request('statut')])>Tous ({{ $total }})</a></li>
            @foreach ($statuses as $value => $label)
                <li>
                    <a href="{{ route('account.reviews.index', ['statut' => $value]) }}" @if (request('statut') === $value) aria-current="true" @endif
                       @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => request('statut') === $value, 'nt-btn-outline' => request('statut') !== $value])>
                        {{ $label }} ({{ $counts[$value] ?? 0 }})
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <x-nt.review-card :review="$review" show-product show-status :interactive="false">
                <x-slot:actions>
                    <span class="text-xs text-muted-foreground"><i class="fa-regular fa-thumbs-up me-1" aria-hidden="true"></i>{{ $review->helpful_count }} personnes ont trouvé cet avis utile</span>
                    <div class="flex gap-2">
                        <x-nt.button variant="outline" size="sm" icon="fa-pen" x-data x-on:click="$dispatch('open-modal', 'edit-review-{{ $review->id }}')">Modifier</x-nt.button>
                        <x-nt.button variant="ghost" size="sm" icon="fa-trash" class="text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-review-{{ $review->id }}')">Supprimer</x-nt.button>
                    </div>
                </x-slot:actions>
            </x-nt.review-card>

            @php
                $bag = 'editReview'.$review->id;
                $failed = $errors->{$bag}->isNotEmpty();
            @endphp
            <x-nt.modal :name="'edit-review-'.$review->id" title="Modifier mon avis" :show="$failed" max-width="xl">
                <form method="POST" action="{{ route('account.reviews.update', $review->id) }}" class="space-y-5" id="edit-review-form-{{ $review->id }}">
                    @csrf
                    @method('PUT')
                    <div>
                        <p class="nt-label">Note</p>
                        <x-nt.rating-stars input name="rating" :value="$failed ? old('rating') : $review->rating" label="Note" size="md" />
                        @error('rating', $bag)<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
                    </div>
                    <x-nt.form.input name="title" label="Titre" :value="$review->title" :bag="$bag" :use-old="$failed" :id="'title-'.$review->id" required maxlength="120" />
                    <x-nt.form.textarea name="body" label="Commentaire" :value="$review->body" :bag="$bag" :use-old="$failed" :id="'body-'.$review->id" required :maxlength="1500" rows="5" />
                </form>
                <x-slot:footer>
                    <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                    <x-nt.button type="submit" form="edit-review-form-{{ $review->id }}" icon="fa-check">Enregistrer</x-nt.button>
                </x-slot:footer>
            </x-nt.modal>

            <x-nt.modal :name="'delete-review-'.$review->id" title="Supprimer cet avis ?" max-width="md">
                <p class="text-sm text-muted-foreground">L'avis « <strong class="text-foreground">{{ $review->title }}</strong> » sera définitivement supprimé. Cette action est irréversible.</p>
                <x-slot:footer>
                    <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                    <form method="POST" action="{{ route('account.reviews.destroy', $review->id) }}">
                        @csrf
                        @method('DELETE')
                        <x-nt.button type="submit" variant="danger" icon="fa-trash">Supprimer</x-nt.button>
                    </form>
                </x-slot:footer>
            </x-nt.modal>
        @empty
            <x-nt.empty-state icon="fa-star" title="Aucun avis pour ce filtre" description="Partagez votre expérience depuis la fiche d'un produit.">
                <x-nt.button :href="route('front.products.index')" icon="fa-basket-shopping">Parcourir les produits</x-nt.button>
            </x-nt.empty-state>
        @endforelse
    </div>
@endsection
