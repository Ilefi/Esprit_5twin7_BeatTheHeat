@php
    $navLinks = [
        ['label' => 'Produits', 'route' => 'front.products.index', 'active' => 'front.products.*', 'icon' => 'fa-basket-shopping'],
        ['label' => 'Traçabilité', 'route' => 'front.traceability.index', 'active' => ['front.traceability.*', 'front.actors.*'], 'icon' => 'fa-route'],
        ['label' => 'Empreinte', 'route' => 'front.impact.index', 'active' => 'front.impact.*', 'icon' => 'fa-leaf'],
        ['label' => 'Certifications', 'route' => 'front.certifications.index', 'active' => 'front.certifications.*', 'icon' => 'fa-award'],
        ['label' => 'Observatoire', 'route' => 'front.observatory', 'active' => ['front.observatory', 'front.reports.*'], 'icon' => 'fa-binoculars'],
    ];
@endphp

<header x-data="{ scrolled: false, menu: false }"
        x-init="scrolled = window.scrollY > 8"
        x-on:scroll.window.passive="scrolled = window.scrollY > 8"
        x-on:keydown.escape.window="menu = false"
        class="sticky top-0 z-40 border-b transition duration-200"
        :class="scrolled ? 'border-border bg-surface/90 shadow-md backdrop-blur-md' : 'border-transparent bg-surface/60 backdrop-blur-sm'">
    <nav class="nt-container flex h-16 items-center gap-6 lg:h-[4.5rem]" aria-label="Navigation principale">
        <a href="{{ route('front.home') }}" class="shrink-0" aria-label="NutriTrace — accueil">
            <x-nt.logo />
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($navLinks as $link)
                @php $isActive = request()->routeIs(...(array) $link['active']); @endphp
                <li>
                    <a href="{{ route($link['route']) }}" @if ($isActive) aria-current="page" @endif
                       @class([
                           'relative rounded-lg px-3 py-2 text-sm font-semibold transition',
                           'text-primary-strong after:absolute after:inset-x-3 after:-bottom-[0.9rem] after:h-0.5 after:rounded-full after:bg-primary' => $isActive,
                           'text-muted-foreground hover:bg-muted hover:text-foreground' => ! $isActive,
                       ])>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="ms-auto flex items-center gap-2">
            <x-nt.button :href="route('front.traceability.index')" size="sm" icon="fa-qrcode" class="hidden sm:inline-flex">Tracer un lot</x-nt.button>

            @guest
                <a href="{{ route('login') }}" class="nt-btn nt-btn-ghost nt-btn-sm hidden md:inline-flex">Connexion</a>
                <a href="{{ route('register') }}" class="nt-btn nt-btn-outline nt-btn-sm hidden md:inline-flex">Inscription</a>
            @endguest

            @auth
                <div class="hidden md:block">
                <x-nt.dropdown label="Menu du compte">
                    <x-slot:trigger class="p-1 pe-2 hover:bg-muted">
                        <x-nt.avatar :name="auth()->user()->name" size="sm" />
                        <span class="hidden max-w-32 truncate text-sm font-semibold xl:inline">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-muted-foreground" aria-hidden="true"></i>
                    </x-slot:trigger>

                    <div class="border-b px-4 py-3">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                    </div>
                    <x-nt.dropdown-link :href="route('account.dashboard')" icon="fa-gauge">Mon espace</x-nt.dropdown-link>
                    <x-nt.dropdown-link :href="route('account.reviews.index')" icon="fa-star">Mes avis</x-nt.dropdown-link>
                    <x-nt.dropdown-link :href="route('account.reports.index')" icon="fa-flag">Mes signalements</x-nt.dropdown-link>
                    @if (auth()->user()->isAdmin())
                        <x-nt.dropdown-link :href="route('admin.dashboard')" icon="fa-user-shield">Administration</x-nt.dropdown-link>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="border-t">
                        @csrf
                        <x-nt.dropdown-link icon="fa-right-from-bracket" danger>Déconnexion</x-nt.dropdown-link>
                    </form>
                </x-nt.dropdown>
                </div>
            @endauth

            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon lg:hidden" x-on:click="menu = true"
                    aria-label="Ouvrir le menu" :aria-expanded="menu.toString()" aria-controls="menu-mobile">
                <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
            </button>
        </div>
    </nav>

    @include('partials.front.mobile-menu', ['links' => $navLinks])
</header>
