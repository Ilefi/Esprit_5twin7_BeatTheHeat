@php
    $columns = [
        'Plateforme' => [
            ['À propos', route('front.about')],
            ['Comment ça marche', route('front.how')],
            ['Observatoire', route('front.observatory')],
            ['Signaler un produit', route('front.reports.create')],
        ],
        'Modules' => [
            ['Produits & Certifications', route('front.products.index')],
            ['Chaîne de traçabilité', route('front.traceability.index')],
            ['Empreinte environnementale', route('front.impact.index')],
            ['Signalements & Avis', route('front.observatory')],
        ],
        'Ressources' => [
            ['Guide des labels', route('front.certifications.index')],
            ['Comparer l\'empreinte', route('front.impact.compare')],
            ['Annuaire des acteurs', route('front.actors.index')],
            ['FAQ', route('front.faq')],
        ],
    ];
@endphp

<footer class="relative overflow-hidden bg-earth text-earth-muted">
    <div class="nt-pattern nt-pattern-light" aria-hidden="true"></div>
    <div class="nt-container relative py-14">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_repeat(4,1fr)]">
            <div class="max-w-sm">
                <a href="{{ route('front.home') }}" aria-label="NutriTrace — accueil"><x-nt.logo variant="light" /></a>
                <p class="mt-4 font-heading text-lg font-semibold text-earth-foreground">« De la ferme à l'assiette, en toute transparence »</p>
                <p class="mt-2 text-sm">Nous rendons visible le parcours de chaque aliment pour que chacun puisse choisir en connaissance de cause — et démasquer le greenwashing.</p>
                <ul class="mt-6 flex gap-2" aria-label="Réseaux sociaux">
                    @foreach (['facebook-f' => 'Facebook', 'instagram' => 'Instagram', 'linkedin-in' => 'LinkedIn', 'youtube' => 'YouTube'] as $icon => $network)
                        <li>
                            <a href="#" class="grid h-10 w-10 place-items-center rounded-full bg-earth-foreground/10 text-earth-foreground transition hover:bg-gold hover:text-gold-foreground" aria-label="NutriTrace sur {{ $network }}">
                                <i class="fa-brands fa-{{ $icon }}" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @foreach ($columns as $heading => $links)
                <nav aria-label="{{ $heading }}">
                    <h2 class="font-heading text-sm font-semibold uppercase tracking-wider text-earth-foreground">{{ $heading }}</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" class="transition hover:text-gold">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            <div>
                <h2 class="font-heading text-sm font-semibold uppercase tracking-wider text-earth-foreground">Contact</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li class="flex gap-2"><i class="fa-solid fa-location-dot mt-1 w-4 text-gold" aria-hidden="true"></i>Esprit, Ariana — Tunisie</li>
                    <li class="flex gap-2"><i class="fa-solid fa-envelope mt-1 w-4 text-gold" aria-hidden="true"></i><a href="mailto:contact@nutritrace.tn" class="hover:text-gold">contact@nutritrace.tn</a></li>
                    <li class="flex gap-2"><i class="fa-solid fa-paper-plane mt-1 w-4 text-gold" aria-hidden="true"></i><a href="{{ route('front.contact') }}" class="hover:text-gold">Formulaire de contact</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-earth-foreground/15 pt-6 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p>© 2026 NutriTrace — Projet académique Esprit 5TWIN</p>
            <p>Données de démonstration · Noms et marques fictifs</p>
        </div>
    </div>
</footer>
