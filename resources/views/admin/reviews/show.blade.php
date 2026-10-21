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
            {{-- Assistant IA de Modération d'Avis --}}
            <div x-data="{
                loading: false,
                analyzed: false,
                data: null,
                analyze() {
                    this.loading = true;
                    fetch('{{ route('admin.reviews.ai.analyze', $review->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.data = res;
                        this.analyzed = true;
                        this.loading = false;
                    })
                    .catch(err => {
                        alert('Erreur lors de l\'analyse IA.');
                        this.loading = false;
                    });
                },
                applyRecommendation() {
                    if (!this.data) return;
                    const noteField = document.querySelector('textarea[name=\'note\']');
                    if (noteField) {
                        noteField.value = 'Analyse IA [' + this.data.sentiment + ' - ' + this.data.confidence + '% de confiance] : ' + this.data.summary;
                    }
                    if (this.data.tone === 'danger') {
                        const rejectedRadio = document.querySelector('input[name=\'status\'][value=\'rejected\']');
                        if (rejectedRadio) rejectedRadio.click();
                    } else if (this.data.tone === 'success') {
                        const publishedRadio = document.querySelector('input[name=\'status\'][value=\'published\']');
                        if (publishedRadio) publishedRadio.click();
                    }
                }
            }" class="nt-card border-primary/30 bg-primary/5 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary text-primary-foreground">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-foreground">Assistant IA Avis Consommateur</h2>
                            <p class="text-xs text-muted-foreground">Sentiment & Authenticité</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="analyze()" :disabled="loading" class="nt-btn nt-btn-primary nt-btn-sm">
                        <template x-if="!loading">
                            <span><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Analyser l'Avis</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fa-solid fa-spinner fa-spin me-1"></i> Analyse en cours...</span>
                        </template>
                    </button>
                </div>

                <div x-show="analyzed" x-transition class="mt-4 space-y-3 border-t border-primary/20 pt-4 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-muted-foreground">Tonalité détectée :</span>
                        <span class="nt-badge" :class="data?.tone === 'danger' ? 'bg-danger text-danger-foreground' : (data?.tone === 'gold' ? 'bg-gold text-gold-foreground' : 'bg-primary text-primary-foreground')">
                            <strong x-text="data?.sentiment"></strong> · <span x-text="data?.confidence + '% confiance'"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-muted-foreground">Authenticité :</span>
                        <span class="font-bold text-foreground" x-text="data?.authenticity"></span>
                    </div>

                    <div class="rounded-lg bg-surface p-3 border">
                        <p class="font-semibold text-foreground">💡 Synthèse sémantique :</p>
                        <p class="mt-1 text-muted-foreground" x-text="data?.summary"></p>
                    </div>

                    <button type="button" x-on:click="applyRecommendation()" class="nt-btn nt-btn-outline nt-btn-sm w-full">
                        <i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i> Insérer la synthèse IA en note
                    </button>
                </div>
            </div>

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
