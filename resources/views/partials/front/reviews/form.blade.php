{{-- "Écrire un avis" — only included for signed-in users (@includeWhen). Errors live in the "review" bag. --}}
<x-nt.card id="ecrire-un-avis">
    <x-slot:header>
        <h3 class="text-base font-semibold"><i class="fa-solid fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>Écrire un avis</h3>
        <span class="text-xs text-muted-foreground">Publié après vérification</span>
    </x-slot:header>

    <form method="POST" action="{{ route('front.reviews.store', $product->slug) }}" class="space-y-5">
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

        <x-nt.form.input name="title" label="Titre de votre avis" bag="review" required maxlength="120" placeholder="Ex. : Une huile au fruité remarquable" />
        <x-nt.form.textarea name="body" label="Votre commentaire" bag="review" required :maxlength="1500" rows="5"
                            hint="Parlez du goût, de la transparence des informations, de l'emballage… (20 caractères min.)" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-muted-foreground"><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>Pas de données personnelles ni de propos injurieux.</p>
            <x-nt.button type="submit" icon="fa-paper-plane">Publier mon avis</x-nt.button>
        </div>
    </form>
</x-nt.card>
