{{-- Shared body of the error pages: @include('partials.shared.error', ['code' => 404, 'title' => …, 'message' => …, 'icon' => 'fa-…']) --}}
<main id="contenu" class="nt-gradient-hero relative flex min-h-screen flex-col overflow-hidden">
    <div class="nt-pattern" aria-hidden="true"></div>

    <header class="nt-container relative py-6">
        <a href="{{ url('/') }}" aria-label="NutriTrace — accueil"><x-nt.logo /></a>
    </header>

    <div class="nt-container relative flex flex-1 flex-col items-center justify-center gap-10 py-10 text-center lg:flex-row lg:text-left">
        <svg viewBox="0 0 320 240" class="w-full max-w-xs shrink-0 lg:max-w-sm" aria-hidden="true" focusable="false">
            <ellipse cx="160" cy="214" rx="130" ry="14" class="fill-foreground/8"/>
            <path class="fill-primary/15" d="M20 214 C70 170 120 190 160 200 S260 170 300 214Z"/>
            <path class="stroke-earth/30" fill="none" stroke-width="3" stroke-dasharray="2 9" stroke-linecap="round" d="M40 190 C90 120 150 210 200 140 S270 90 290 60"/>
            <text x="160" y="150" text-anchor="middle" class="fill-primary font-heading text-[96px] font-extrabold">{{ $code }}</text>
            {{-- Outer group positions, inner group floats (a CSS transform would override the transform attribute). --}}
            <g transform="translate(262 44)">
                <g class="nt-float">
                    <circle r="26" class="fill-gold"/>
                    <path class="fill-primary" d="M-2 -14 C10 -12 14 -2 12 8 C2 10 -8 6 -10 -4 C-10 -9 -7 -13 -2 -14Z"/>
                    <path class="stroke-primary-foreground" stroke-width="2" stroke-linecap="round" fill="none" d="M-6 6 L8 -8"/>
                </g>
            </g>
            <circle cx="40" cy="190" r="6" class="fill-earth"/>
        </svg>

        <div class="max-w-lg">
            <p class="nt-eyebrow mb-3"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i> Erreur {{ $code }}</p>
            <h1 class="text-3xl font-extrabold sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 text-lg text-muted-foreground">{{ $message }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3 lg:justify-start">
                @foreach ($actions ?? [['Retour à l\'accueil', url('/'), 'fa-house', 'primary']] as [$label, $url, $actionIcon, $variant])
                    <x-nt.button :href="$url" :variant="$variant" :icon="$actionIcon">{{ $label }}</x-nt.button>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="nt-container relative py-6 text-center text-xs text-muted-foreground">© 2026 NutriTrace — Projet académique Esprit 5TWIN</footer>
</main>
