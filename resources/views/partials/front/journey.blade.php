{{-- Animated farm → factory → shop → plate illustration (inline SVG, token colors only). --}}
<div class="relative mx-auto w-full max-w-xl" aria-hidden="true">
    <svg viewBox="0 0 560 440" class="h-auto w-full" focusable="false">
        <defs>
            <clipPath id="journey-scene"><circle cx="290" cy="215" r="215"/></clipPath>
        </defs>
        <circle cx="290" cy="215" r="215" class="fill-primary/8"/>
        <circle cx="430" cy="90" r="60" class="fill-gold/25 nt-float"/>

        {{-- Hills, clipped to the round scene --}}
        <g clip-path="url(#journey-scene)">
            <path class="fill-primary/15" d="M0 330 C90 280 170 310 250 325 S420 290 560 315 V440 H0Z"/>
            <path class="fill-primary/25" d="M0 375 C120 340 220 365 320 360 S480 335 560 352 V440 H0Z"/>
            @foreach ([180, 210, 240, 270] as $x)
                <path class="stroke-primary/40" fill="none" stroke-width="3" stroke-linecap="round" d="M{{ $x }} 410 q8 -14 0 -26"/>
            @endforeach
        </g>

        {{-- Track (dotted) + animated line --}}
        <path class="stroke-earth/25" fill="none" stroke-width="3" stroke-dasharray="2 10" stroke-linecap="round"
              d="M95 318 C150 318 160 222 225 212 S320 150 362 176 S440 250 478 132"/>
        <path class="nt-draw-line stroke-primary" fill="none" stroke-width="4" stroke-linecap="round" pathLength="1000" style="--nt-path-length: 1000"
              d="M95 318 C150 318 160 222 225 212 S320 150 362 176 S440 250 478 132"/>

        {{-- Farm --}}
        <g transform="translate(95 318)">
            <circle r="42" class="fill-surface stroke-border" stroke-width="2"/>
            <path class="fill-earth" d="M-18 14 V-4 L0 -18 L18 -4 V14 Z"/>
            <path class="fill-gold" d="M-6 14 V2 H6 V14 Z"/>
            <path class="stroke-earth-foreground" stroke-width="1.6" d="M-6 2 L6 14 M6 2 L-6 14"/>
            <path class="fill-primary" d="M-30 14 h10 v-12 a5 5 0 0 0 -10 0Z"/>
        </g>
        {{-- Factory --}}
        <g transform="translate(225 212)">
            <circle r="42" class="fill-surface stroke-border" stroke-width="2"/>
            <path class="fill-earth" d="M-20 16 V-2 L-10 -10 V-2 L0 -10 V-2 L10 -10 V16 Z"/>
            <rect x="11" y="-20" width="7" height="36" rx="1" class="fill-earth"/>
            <circle cx="20" cy="-27" r="5" class="fill-muted-foreground/30"/>
            <rect x="-14" y="4" width="6" height="6" class="fill-gold"/>
            <rect x="-3" y="4" width="6" height="6" class="fill-gold"/>
        </g>
        {{-- Shop --}}
        <g transform="translate(362 176)">
            <circle r="42" class="fill-surface stroke-border" stroke-width="2"/>
            <rect x="-18" y="-2" width="36" height="20" rx="2" class="fill-primary"/>
            <path class="fill-gold" d="M-21 -2 L-17 -14 H17 L21 -2 Z"/>
            <path class="stroke-surface" stroke-width="3" d="M-9 -14 L-11 -2 M0 -14 V-2 M9 -14 L11 -2"/>
            <rect x="-5" y="6" width="10" height="12" class="fill-surface"/>
        </g>
        {{-- Plate --}}
        <g transform="translate(478 132)">
            <circle r="42" class="fill-primary"/>
            <circle r="22" class="fill-surface"/>
            <circle r="14" class="fill-none stroke-border" stroke-width="2"/>
            <path class="fill-primary-light" d="M-6 4 C-6 -6 2 -10 8 -10 C8 -2 4 6 -6 4Z"/>
            <path class="stroke-surface" stroke-width="2.4" stroke-linecap="round" d="M-31 -10 V10 M-35 -10 V-2 M-27 -10 V-2 M31 -10 V10"/>
        </g>

        {{-- Labels --}}
        <g class="fill-foreground font-heading text-[13px] font-semibold" text-anchor="middle">
            <text x="95" y="380">Ferme</text>
            <text x="225" y="274">Transformation</text>
            <text x="362" y="238">Magasin</text>
            <text x="478" y="194">Assiette</text>
        </g>
    </svg>

    <div class="nt-card nt-float absolute -left-2 top-6 hidden items-center gap-3 px-4 py-3 shadow-lg sm:flex">
        <span class="grid h-10 w-10 place-items-center rounded-full bg-primary/12 text-primary-strong"><i class="fa-solid fa-circle-check"></i></span>
        <div>
            <p class="text-xs text-muted-foreground">Lot NT-2026-OLV-0412</p>
            <p class="text-sm font-semibold">6 étapes vérifiées</p>
        </div>
    </div>
    <div class="nt-card nt-float absolute -bottom-2 right-0 hidden items-center gap-3 px-4 py-3 shadow-lg [animation-delay:1.5s] sm:flex">
        <x-nt.eco-score grade="B" size="sm" />
        <div>
            <p class="text-xs text-muted-foreground">Empreinte</p>
            <p class="text-sm font-semibold">280 km parcourus</p>
        </div>
    </div>
</div>
