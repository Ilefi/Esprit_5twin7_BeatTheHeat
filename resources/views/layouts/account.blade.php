{{-- Consumer space. Level 3 of the inheritance chain: master → front → account. --}}
@extends('layouts.front')

@php
    $accountLinks = [
        ['Tableau de bord', 'account.dashboard', 'account.dashboard', 'fa-gauge'],
        ['Mes avis', 'account.reviews.index', 'account.reviews.*', 'fa-star'],
        ['Mes signalements', 'account.reports.index', 'account.reports.*', 'fa-flag'],
        ['Mon profil', 'profile.edit', 'profile.*', 'fa-user-gear'],
    ];
@endphp

{{-- No newsletter inside the private space. --}}
@section('pre_footer')
@endsection

@section('content')
    <div class="nt-container py-8 lg:py-12">
        @include('partials.shared.breadcrumb', ['items' => array_merge(
            [['label' => 'Accueil', 'url' => route('front.home')], ['label' => 'Mon espace', 'url' => route('account.dashboard')]],
            View::hasSection('account_breadcrumb') ? [['label' => trim(View::yieldContent('account_breadcrumb'))]] : []
        ), 'class' => 'mb-6'])

        <div class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <aside aria-label="Mon espace">
                <div class="nt-card p-5 lg:sticky lg:top-24">
                    <div class="flex items-center gap-3">
                        <x-nt.avatar :name="auth()->user()->name" size="lg" />
                        <div class="min-w-0">
                            <p class="truncate font-heading font-semibold">{{ auth()->user()->name }}</p>
                            <x-status-badge type="role" :value="auth()->user()->role ?? 'consumer'" />
                        </div>
                    </div>
                    <nav class="mt-5 -mx-2 flex gap-1 overflow-x-auto border-t pt-4 lg:flex-col" aria-label="Rubriques">
                        @foreach ($accountLinks as [$label, $route, $pattern, $icon])
                            <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                               @class([
                                   'flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition',
                                   'bg-primary/10 text-primary-strong' => request()->routeIs($pattern),
                                   'text-muted-foreground hover:bg-muted hover:text-foreground' => ! request()->routeIs($pattern),
                               ])>
                                <i class="fa-solid {{ $icon }} w-4 text-center" aria-hidden="true"></i>{{ $label }}
                            </a>
                        @endforeach
                    </nav>
                    <a href="{{ route('front.reports.create') }}" class="nt-btn nt-btn-danger nt-btn-sm mt-4 hidden w-full lg:flex">
                        <i class="fa-solid fa-flag" aria-hidden="true"></i> Nouveau signalement
                    </a>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold sm:text-3xl">@yield('account_title')</h1>
                        @hasSection('account_subtitle')
                            <p class="mt-1 text-muted-foreground">@yield('account_subtitle')</p>
                        @endif
                    </div>
                    @hasSection('account_actions')
                        <div class="flex flex-wrap gap-2">@yield('account_actions')</div>
                    @endif
                </header>

                @yield('account_content')
            </div>
        </div>
    </div>
@endsection
