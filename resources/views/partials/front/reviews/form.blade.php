{{-- "Écrire un avis" — only included for signed-in users (@includeWhen). Errors live in the "review" bag. --}}
<x-nt.card id="ecrire-un-avis">
    <x-slot:header>
        <h3 class="text-base font-semibold"><i class="fa-solid fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>Écrire un avis</h3>
        <span class="text-xs text-muted-foreground">Publié après vérification</span>
    </x-slot:header>

    <form method="POST" action="{{ route('front.reviews.store', $product->slug) }}" class="space-y-5"
          x-data="{ title: @js(old('title', '')), body: @js(old('body', '')) }">
        @csrf

        <div>
            <p class="nt-label" id="rating-label">Note globale <span class="text-danger-strong" aria-hidden="true">*</span></p>
            <x-nt.rating-stars input name="rating" :value="old('rating')" label="Note globale" size="lg" />
            @error('rating', 'review')
                <p class="mt-1 text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach (['quality_rating' => 'Qualité', 'transparency_rating' => 'Transparence', 'value_rating' => 'Rapport qualité/prix'] as $field => $label)
                <div class="rounded-lg bg-muted/50 p-3">
                    <p class="text-xs font-semibold">{{ $label }}</p>
                    <x-nt.rating-stars input :name="$field" :value="old($field)" :label="$label" size="sm" class="[&>span:last-child]:hidden" />
                    @error($field, 'review')
                        <p class="mt-1 text-xs text-danger-strong">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div>
            <label for="review-title" class="nt-label">Titre de votre avis <span class="text-danger-strong" aria-hidden="true">*</span></label>
            <input id="review-title" name="title" type="text" x-model="title" maxlength="120" required placeholder="Ex. : Une huile au fruité remarquable"
                   class="nt-input transition"
                   :class="title.length > 0 && title.trim().length < 3 ? '!border-danger !ring-2 !ring-danger/20' : (title.trim().length >= 3 ? '!border-primary' : '')">
            <div class="mt-1 flex items-center justify-between text-xs">
                @error('title', 'review')
                    <p class="text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>
                @else
                    <div>
                        <template x-if="title.length > 0 && title.trim().length < 3">
                            <p class="font-semibold text-danger-strong flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i> Titre trop court : au moins 3 caractères requis.
                            </p>
                        </template>
                    </div>
                @enderror
                <span class="tabular-nums text-muted-foreground ms-auto" x-text="title.length + ' / 120'"></span>
            </div>
        </div>

        <div>
            <label for="review-body" class="nt-label">Votre commentaire <span class="text-danger-strong" aria-hidden="true">*</span></label>
            <textarea id="review-body" name="body" rows="5" maxlength="1500" x-model="body" required
                      class="nt-input transition"
                      :class="body.length > 0 && body.trim().length < 20 ? '!border-danger !ring-2 !ring-danger/20' : (body.trim().length >= 20 ? '!border-primary' : '')"
                      placeholder="Parlez du goût, de la transparence des informations, de l'emballage… (20 caractères min.)"></textarea>
            <div class="mt-1.5 flex flex-wrap items-center justify-between gap-3 text-xs">
                @error('body', 'review')
                    <p class="text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>
                @else
                    <div>
                        <template x-if="body.length === 0">
                            <p class="text-muted-foreground">Parlez du goût, de la qualité, de l'emballage… (20 caractères min.)</p>
                        </template>
                        <template x-if="body.length > 0 && body.trim().length < 20">
                            <p class="font-semibold text-danger-strong flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                Commentaire trop court : il manque encore <span x-text="20 - body.trim().length"></span> caractère(s) (min. 20).
                            </p>
                        </template>
                        <template x-if="body.trim().length >= 20">
                            <p class="font-semibold text-primary flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i>
                                Longueur conforme (min. 20 caractères).
                            </p>
                        </template>
                    </div>
                @enderror
                <p class="tabular-nums" :class="body.length > 0 && body.trim().length < 20 ? 'text-danger-strong font-bold' : 'text-muted-foreground'">
                    <span x-text="body.length"></span> / 1500
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs">
                <template x-if="body.length > 0 && body.trim().length < 20">
                    <span class="font-semibold text-danger-strong flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> Min. 20 caractères requis pour le commentaire
                    </span>
                </template>
                <template x-if="body.trim().length >= 20 || body.length === 0">
                    <p class="text-muted-foreground"><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>Pas de données personnelles ni de propos injurieux.</p>
                </template>
            </div>
            <x-nt.button type="submit" icon="fa-paper-plane" ::disabled="title.trim().length < 3 || body.trim().length < 20">Publier mon avis</x-nt.button>
        </div>
    </form>
</x-nt.card>
