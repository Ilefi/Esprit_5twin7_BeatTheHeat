<section class="nt-section pt-0" aria-labelledby="newsletter-title">
    <div class="nt-container">
        <div class="nt-gradient-primary relative overflow-hidden rounded-xl px-6 py-10 text-primary-foreground shadow-lg sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:px-14">
            <div class="nt-pattern nt-pattern-light" aria-hidden="true"></div>
            <div class="relative max-w-xl">
                <p class="font-heading text-sm font-semibold uppercase tracking-wider text-gold">La lettre NutriTrace</p>
                <h2 id="newsletter-title" class="mt-2 text-2xl font-bold text-primary-foreground sm:text-3xl">Chaque mois, un produit décrypté de la ferme à l'assiette.</h2>
                <p class="mt-2 text-primary-foreground">Nouveaux lots tracés, labels expliqués et cas de greenwashing démasqués. Zéro spam.</p>
            </div>
            <form method="POST" action="{{ route('front.newsletter') }}" class="relative mt-6 flex w-full max-w-md flex-col gap-3 sm:flex-row lg:mt-0">
                @csrf
                <label for="newsletter-email" class="sr-only">Adresse e-mail</label>
                <input id="newsletter-email" type="email" name="newsletter_email" required placeholder="vous@exemple.tn" autocomplete="email"
                       value="{{ old('newsletter_email') }}" class="nt-input flex-1 border-transparent" @error('newsletter_email') aria-invalid="true" aria-describedby="newsletter-error" @enderror>
                <button type="submit" class="nt-btn nt-btn-gold">S'abonner <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
                @error('newsletter_email')
                    <p id="newsletter-error" class="w-full rounded bg-surface px-2 py-1 text-sm text-danger-strong sm:absolute sm:-bottom-9">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
</section>
