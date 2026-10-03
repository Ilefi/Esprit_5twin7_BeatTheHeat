{{-- Split-screen authentication layout (Breeze views extend this). --}}
@extends('layouts.master')

@section('body')
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <aside class="nt-gradient-primary relative hidden overflow-hidden text-primary-foreground lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <div class="nt-pattern nt-pattern-light" aria-hidden="true"></div>

            <a href="{{ route('front.home') }}" class="relative" aria-label="NutriTrace — accueil">
                <x-nt.logo variant="light" />
            </a>

            <div class="relative">
                <p class="font-heading text-sm font-semibold uppercase tracking-wider text-gold">Traçabilité alimentaire</p>
                <h2 class="mt-3 text-4xl font-extrabold leading-tight text-primary-foreground xl:text-5xl">De la ferme à l'assiette, en toute transparence</h2>
                <p class="mt-4 max-w-md text-lg text-primary-foreground">Suivez chaque produit, vérifiez ses labels et faites entendre votre voix face au greenwashing.</p>

                <ol class="mt-10 flex max-w-md items-center justify-between" aria-label="La chaîne de traçabilité">
                    @foreach ([['fa-tractor', 'Producteur'], ['fa-industry', 'Transformateur'], ['fa-truck', 'Distributeur'], ['fa-utensils', 'Consommateur']] as [$icon, $label])
                        <li class="flex flex-1 items-center">
                            <div class="flex flex-col items-center gap-2">
                                <span class="grid h-12 w-12 place-items-center rounded-full bg-primary-foreground/15 text-lg ring-1 ring-primary-foreground/30">
                                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                </span>
                                <span class="text-xs font-medium">{{ $label }}</span>
                            </div>
                            @unless ($loop->last)
                                <span class="mx-1 mb-6 h-px flex-1 border-t-2 border-dashed border-gold/70" aria-hidden="true"></span>
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </div>

            <p class="relative text-sm text-primary-foreground">© 2026 NutriTrace — Projet académique Esprit 5TWIN</p>
        </aside>

        <main id="contenu" tabindex="-1" class="nt-gradient-hero relative flex flex-col justify-center px-4 py-10 focus:outline-none sm:px-8">
            <div class="mx-auto w-full max-w-md">
                <a href="{{ route('front.home') }}" class="mb-8 inline-flex lg:hidden" aria-label="NutriTrace — accueil">
                    <x-nt.logo />
                </a>

                <div class="nt-card p-6 shadow-lg sm:p-8">
                    <header class="mb-6">
                        <h1 class="text-2xl font-bold">@yield('auth_title')</h1>
                        @hasSection('auth_subtitle')
                            <p class="mt-1.5 text-sm text-muted-foreground">@yield('auth_subtitle')</p>
                        @endif
                    </header>

                    @yield('content')
                </div>

                @hasSection('auth_footer')
                    <p class="mt-6 text-center text-sm text-muted-foreground">@yield('auth_footer')</p>
                @endif

                <p class="mt-6 text-center">
                    <a href="{{ route('front.home') }}" class="nt-link text-sm"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Retour au site</a>
                </p>
            </div>
        </main>
    </div>
@endsection
