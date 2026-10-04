<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b bg-surface/95 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon lg:hidden" x-on:click="sidebar = true"
            aria-label="Ouvrir le menu" aria-controls="admin-sidebar" :aria-expanded="sidebar.toString()">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon hidden lg:inline-flex" x-on:click="collapsed = ! collapsed"
            :aria-label="collapsed ? 'Déplier la barre latérale' : 'Replier la barre latérale'" :aria-expanded="(! collapsed).toString()" aria-controls="admin-sidebar">
        <i class="fa-solid fa-angles-left transition" :class="collapsed && 'rotate-180'" aria-hidden="true"></i>
    </button>

    <form action="{{ route('admin.products.index') }}" method="GET" role="search" class="relative hidden max-w-md flex-1 md:block">
        <label for="admin-search" class="sr-only">Rechercher dans l'administration</label>
        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground" aria-hidden="true"></i>
        <input id="admin-search" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un produit, un lot, un signalement…" class="nt-input bg-muted/50 ps-10">
    </form>

    <div class="ms-auto flex items-center gap-1 sm:gap-2">
        <a href="{{ route('front.home') }}" class="nt-btn nt-btn-ghost nt-btn-sm hidden sm:inline-flex">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Voir le site
        </a>

        <x-nt.dropdown width="w-80" label="Notifications ({{ $notifications->count() }})">
            <x-slot:trigger class="nt-btn nt-btn-ghost nt-btn-icon relative">
                <i class="fa-regular fa-bell text-lg" aria-hidden="true"></i>
                <span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-danger ring-2 ring-surface" aria-hidden="true"></span>
            </x-slot:trigger>

            <p class="border-b px-4 py-3 text-sm font-semibold">Notifications</p>
            <ul class="max-h-80 divide-y overflow-y-auto">
                @php
                    $tones = ['danger' => 'bg-danger/10 text-danger-strong', 'warning' => 'bg-warning/15 text-warning-strong', 'gold' => 'bg-gold/25 text-gold-strong', 'info' => 'bg-info/12 text-info-strong'];
                @endphp
                @foreach ($notifications as $notification)
                    <li class="flex gap-3 px-4 py-3 hover:bg-muted/60">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $tones[$notification->tone] }}">
                            <i class="fa-solid {{ $notification->icon }} text-sm" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0 text-sm">
                            <p class="font-semibold">{{ $notification->title }}</p>
                            <p class="truncate text-muted-foreground">{{ $notification->body }}</p>
                            <p class="text-xs text-muted-foreground">{{ $notification->at->diffForHumans() }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('admin.reports.index') }}" class="block border-t px-4 py-2.5 text-center text-sm font-semibold text-primary-strong hover:bg-muted">Tout voir</a>
        </x-nt.dropdown>

        <x-nt.dropdown label="Menu utilisateur">
            <x-slot:trigger class="p-1 pe-2 hover:bg-muted">
                <x-nt.avatar :name="auth()->user()->name" size="sm" />
                <span class="hidden text-sm font-semibold lg:inline">{{ auth()->user()->name }}</span>
                <i class="fa-solid fa-chevron-down text-xs text-muted-foreground" aria-hidden="true"></i>
            </x-slot:trigger>

            <div class="border-b px-4 py-3">
                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                <x-status-badge type="role" :value="auth()->user()->role" class="mt-1" />
            </div>
            <x-nt.dropdown-link :href="route('profile.edit')" icon="fa-user-gear">Mon profil</x-nt.dropdown-link>
            <x-nt.dropdown-link :href="route('account.dashboard')" icon="fa-gauge">Espace consommateur</x-nt.dropdown-link>
            <x-nt.dropdown-link :href="route('front.home')" icon="fa-globe">Voir le site</x-nt.dropdown-link>
            <form method="POST" action="{{ route('logout') }}" class="border-t">
                @csrf
                <x-nt.dropdown-link icon="fa-right-from-bracket" danger>Déconnexion</x-nt.dropdown-link>
            </form>
        </x-nt.dropdown>
    </div>
</header>
