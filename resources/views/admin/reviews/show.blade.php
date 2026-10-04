@extends('layouts.admin')

@section('title', 'Avis — '.$review->title)
@section('page_title', 'Examen de l\'avis')
@section('page_subtitle', $review->product->name.' · par '.$review->user->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Avis', 'url' => route('admin.reviews.index')], ['label' => '#'.$review->id]]])
@endsection

@section('page_actions')
    <x-nt.button variant="ghost" size="sm" icon="fa-trash" class="text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-review')">Supprimer</x-nt.button>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            @if ($review->status === 'flagged')
                <x-nt.alert type="danger" title="Avis signalé par la communauté">
                    {{ $review->history->firstWhere('label', 'Signalé par la communauté')?->note ?? 'Cet avis a été signalé et nécessite un examen.' }}
                </x-nt.alert>
            @endif

            <x-nt.review-card :review="$review" show-product show-status :interactive="false" />

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Décision de modération</h2></x-slot:header>
                <form method="POST" action="{{ route('admin.reviews.moderate', $review->id) }}" class="space-y-4" x-data="{ status: @js(old('status', $review->status === 'pending' ? 'published' : $review->status)) }">
                    @csrf
                    @method('PATCH')
                    <fieldset>
                        <legend class="nt-label">Statut</legend>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach (['published' => ['Publier', 'fa-check'], 'flagged' => ['Garder signalé', 'fa-flag'], 'rejected' => ['Rejeter', 'fa-xmark']] as $value => [$label, $icon])
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $value }}" x-model="status" class="peer sr-only" @checked(old('status', $review->status) === $value)>
                                    <span class="flex items-center justify-center gap-2 rounded-lg border-2 p-3 text-sm font-semibold transition peer-checked:border-primary peer-checked:bg-primary/6 peer-focus-visible:ring-2 peer-focus-visible:ring-ring">
                                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-nt.form.textarea name="note" label="Note de modération (interne)" rows="2" :maxlength="500" hint="Visible uniquement par l'équipe." />
                    <div class="flex justify-end">
                        <x-nt.button type="submit" icon="fa-gavel">Appliquer la décision</x-nt.button>
                    </div>
                </form>
            </x-nt.card>
        </div>

        <div class="space-y-6">
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Contexte</h2></x-slot:header>
                <div class="flex items-center gap-3">
                    <x-nt.product-image :product="$review->product" size="sm" class="h-14 w-14 shrink-0 rounded-lg" />
                    <div class="min-w-0">
                        <a href="{{ route('admin.products.show', $review->product->id) }}" class="block truncate font-semibold hover:text-primary-strong">{{ $review->product->name }}</a>
                        <p class="text-xs text-muted-foreground">Note moyenne {{ number_format($review->product->rating_avg, 1, ',', ' ') }} · {{ $review->product->reviews_count }} avis</p>
                    </div>
                </div>
                <dl class="mt-5 space-y-2 border-t pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-muted-foreground">Auteur</dt><dd class="font-medium">{{ $review->user->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Avis rédigés</dt><dd class="font-medium">{{ $review->user->reviews_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Achat vérifié</dt><dd class="font-medium">{{ $review->verified_purchase ? 'Oui' : 'Non' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Votes « utile »</dt><dd class="font-medium">{{ $review->helpful_count }}</dd></div>
                </dl>
                @if ($otherReviews->isNotEmpty())
                    <h3 class="mt-5 text-sm font-semibold">Autres avis de cet auteur</h3>
                    <ul class="mt-2 space-y-2">
                        @foreach ($otherReviews->take(4) as $other)
                            <li class="flex items-center gap-2 text-sm">
                                <a href="{{ route('admin.reviews.show', $other->id) }}" class="min-w-0 flex-1 truncate hover:text-primary-strong">{{ $other->title }}</a>
                                <x-status-badge type="review" :value="$other->status" :icon="false" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-nt.card>

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Historique de modération</h2></x-slot:header>
                <x-nt.timeline>
                    @foreach ($review->history as $entry)
                        <x-nt.timeline-item icon="fa-clock-rotate-left" :tone="$loop->last ? 'primary' : 'muted'" :title="$entry->label" :time="$entry->created_at->translatedFormat('d M Y, H:i').' · '.$entry->author">
                            {{ $entry->note }}
                        </x-nt.timeline-item>
                    @endforeach
                </x-nt.timeline>
            </x-nt.card>
        </div>
    </div>

    @include('partials.admin.delete-modal', ['name' => 'delete-review', 'action' => route('admin.reviews.destroy', $review->id), 'label' => 'l\'avis « '.$review->title.' »'])
@endsection
