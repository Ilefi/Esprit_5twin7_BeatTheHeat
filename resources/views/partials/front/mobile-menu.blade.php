{{-- Slide-over menu (< lg). Lives inside the navbar's Alpine scope ("menu"). --}}
<div x-show="menu" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu" id="menu-mobile">
    <div x-show="menu" x-transition.opacity class="absolute inset-0 bg-foreground/50 backdrop-blur-sm" x-on:click="menu = false"></div>

    <div x-show="menu"
         x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         x-effect="document.body.classList.toggle('overflow-hidden', menu); if (menu) $nextTick(() => $el.querySelector('button')?.focus())"
         class="absolute inset-y-0 right-0 flex w-full max-w-xs flex-col overflow-y-auto bg-surface shadow-lg">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <x-nt.logo />
            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon" x-on:click="menu = false" aria-label="Fermer le menu">
                <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </div>

        <ul class="space-y-1 p-4">
            @foreach ($links as $link)
                @php $isActive = request()->routeIs(...(array) $link['active']); @endphp
                <li>
                    <a href="{{ route($link['route']) }}" @if ($isActive) aria-current="page" @endif
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-3 font-semibold',
                           'bg-primary/10 text-primary-strong' => $isActive,
                           'text-foreground hover:bg-muted' => ! $isActive,
                       ])>
                        <i class="fa-solid {{ $link['icon'] }} w-5 text-center" aria-hidden="true"></i>{{ $link['label'] }}
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ route('front.actors.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-3 font-semibold hover:bg-muted">
                    <i class="fa-solid fa-people-group w-5 text-center" aria-hidden="true"></i>Acteurs
                </a>
            </li>
        </ul>

        <div class="mt-auto space-y-3 border-t p-4">
            <x-nt.button :href="route('front.traceability.index')" icon="fa-qrcode" class="w-full">Tracer un lot</x-nt.button>

            @guest
                <div class="grid grid-cols-2 gap-3">
                    <x-nt.button :href="route('login')" variant="outline">Connexion</x-nt.button>
                    <x-nt.button :href="route('register')" variant="secondary">Inscription</x-nt.button>
                </div>
            @else
                <div class="flex items-center gap-3 rounded-lg bg-muted/60 p-3">
                    <x-nt.avatar :name="auth()->user()->name" size="sm" />
                    <p class="min-w-0 flex-1 truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                </div>
                <nav aria-label="Mon compte" class="grid gap-1 text-sm">
                    <a href="{{ route('account.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-muted"><i class="fa-solid fa-gauge me-2 w-4" aria-hidden="true"></i>Mon espace</a>
                    <a href="{{ route('account.reviews.index') }}" class="rounded-lg px-3 py-2 hover:bg-muted"><i class="fa-solid fa-star me-2 w-4" aria-hidden="true"></i>Mes avis</a>
                    <a href="{{ route('account.reports.index') }}" class="rounded-lg px-3 py-2 hover:bg-muted"><i class="fa-solid fa-flag me-2 w-4" aria-hidden="true"></i>Mes signalements</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-muted"><i class="fa-solid fa-user-shield me-2 w-4" aria-hidden="true"></i>Administration</a>
                    @endif
                </nav>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-nt.button type="submit" variant="ghost" icon="fa-right-from-bracket" class="w-full text-danger-strong">Déconnexion</x-nt.button>
                </form>
            @endguest
        </div>
    </div>
</div>
