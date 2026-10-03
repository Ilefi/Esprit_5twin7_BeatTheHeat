{{-- Counts come from App\View\Composers\AdminSidebarComposer. --}}
@php
    $groups = [
        null => [
            ['Tableau de bord', 'admin.dashboard', ['admin.dashboard'], 'fa-gauge-high', null],
        ],
        'Produits & Certifications' => [
            ['Produits', 'admin.products.index', ['admin.products.*'], 'fa-basket-shopping', null],
            ['Catégories', 'admin.categories.index', ['admin.categories.*'], 'fa-tags', null],
            ['Certifications', 'admin.certifications.index', ['admin.certifications.index', 'admin.certifications.create', 'admin.certifications.edit'], 'fa-award', null],
            ['Vérifications', 'admin.certifications.verifications', ['admin.certifications.verifications*'], 'fa-file-circle-check', $pendingVerificationsCount],
        ],
        'Traçabilité' => [
            ['Acteurs', 'admin.actors.index', ['admin.actors.*'], 'fa-people-group', null],
            ['Lots', 'admin.batches.index', ['admin.batches.*'], 'fa-boxes-stacked', null],
        ],
        'Empreinte environnementale' => [
            ['Impacts produits', 'admin.impacts.index', ['admin.impacts.index', 'admin.impacts.create', 'admin.impacts.edit'], 'fa-leaf', null],
            ['Facteurs d\'émission', 'admin.impacts.factors', ['admin.impacts.factors'], 'fa-table-list', null],
        ],
        'Signalements & Avis' => [
            ['Signalements', 'admin.reports.index', ['admin.reports.*'], 'fa-flag', $pendingReportsCount],
            ['Avis', 'admin.reviews.index', ['admin.reviews.*'], 'fa-star-half-stroke', $pendingReviewsCount],
        ],
        'Utilisateurs' => [
            ['Utilisateurs', 'admin.users.index', ['admin.users.*'], 'fa-users', null],
        ],
    ];
@endphp

{{-- Mobile backdrop --}}
<div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-foreground/50 lg:hidden" x-on:click="sidebar = false"></div>

<aside id="admin-sidebar" aria-label="Navigation d'administration"
       class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-earth text-earth-muted transition-all duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
       :class="{ 'translate-x-0!': sidebar, 'lg:w-20': collapsed, 'lg:w-72': ! collapsed }">
    <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-earth-foreground/10 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2" aria-label="Tableau de bord NutriTrace">
            <x-nt.logo variant="light" x-bind:class="collapsed && 'lg:[&>span:last-child]:sr-only'" />
        </a>
        <button type="button" class="nt-btn nt-btn-icon text-earth-foreground hover:bg-earth-foreground/10 lg:hidden" x-on:click="sidebar = false" aria-label="Fermer le menu">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        @foreach ($groups as $heading => $items)
            <div>
                @if ($heading)
                    <p class="mb-2 px-3 text-[0.7rem] font-semibold uppercase tracking-wider text-earth-muted/80" :class="collapsed && 'lg:sr-only'">{{ $heading }}</p>
                @endif
                <ul class="space-y-1">
                    @foreach ($items as [$label, $route, $patterns, $icon, $count])
                        @php $isActive = request()->routeIs(...$patterns); @endphp
                        <li>
                            <a href="{{ route($route) }}" @if ($isActive) aria-current="page" @endif title="{{ $label }}"
                               @class([
                                   'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                   'bg-primary text-primary-foreground shadow-md' => $isActive,
                                   'hover:bg-earth-foreground/10 hover:text-earth-foreground' => ! $isActive,
                               ])>
                                <i class="fa-solid {{ $icon }} w-5 shrink-0 text-center" aria-hidden="true"></i>
                                <span class="flex-1 truncate" :class="collapsed && 'lg:sr-only'">{{ $label }}</span>
                                @if ($count)
                                    <span class="nt-badge bg-gold px-2 py-0 text-gold-foreground" :class="collapsed && 'lg:hidden'">
                                        {{ $count }}<span class="sr-only"> en attente</span>
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-earth-foreground/10 p-3">
        <a href="{{ route('front.home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-earth-foreground/10 hover:text-earth-foreground" title="Voir le site">
            <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center" aria-hidden="true"></i>
            <span :class="collapsed && 'lg:sr-only'">Voir le site</span>
        </a>
    </div>
</aside>
