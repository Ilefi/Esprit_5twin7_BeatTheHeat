@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')
@section('page_subtitle', 'Vue d\'ensemble de la plateforme — '.now()->translatedFormat('l d F Y'))

@section('page_actions')
    <x-nt.button :href="route('admin.reports.index', ['vue' => 'kanban'])" variant="outline" size="sm" icon="fa-table-columns">Kanban signalements</x-nt.button>
    <x-nt.button :href="route('admin.products.create')" size="sm" icon="fa-plus">Nouveau produit</x-nt.button>
@endsection

@section('content')
    {{-- KPIs --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        @foreach ($kpis as $kpi)
            <x-nt.stat-card :icon="$kpi['icon']" :value="$kpi['value']" :label="$kpi['label']" :trend="$kpi['trend']" :trend-good="$kpi['good']" :tone="$kpi['tone']" :href="route($kpi['route'])" />
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-nt.card class="xl:col-span-2">
            <x-slot:header>
                <h2 class="text-base font-semibold">Avis et signalements par mois</h2>
                <span class="text-xs text-muted-foreground">12 derniers mois</span>
            </x-slot:header>
            <div class="h-72"><canvas x-data="ntChart(@js($charts['activity']))" role="img" aria-label="Évolution mensuelle des avis et des signalements"></canvas></div>
        </x-nt.card>
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Signalements par statut</h2></x-slot:header>
            <div class="h-72"><canvas x-data="ntChart(@js($charts['status']))" role="img" aria-label="Répartition des signalements par statut"></canvas></div>
        </x-nt.card>
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Produits par éco-score</h2></x-slot:header>
            <div class="h-64"><canvas x-data="ntChart(@js($charts['eco']))" role="img" aria-label="Nombre de produits par éco-score"></canvas></div>
        </x-nt.card>
        <x-nt.card class="xl:col-span-2">
            <x-slot:header><h2 class="text-base font-semibold">Produits certifiés par label</h2></x-slot:header>
            <div class="h-64"><canvas x-data="ntChart(@js($charts['certifications']))" role="img" aria-label="Nombre de produits par certification"></canvas></div>
        </x-nt.card>
    </div>

    {{-- Critical alerts + quick actions --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-nt.card class="border-danger/30 xl:col-span-2">
            <x-slot:header>
                <h2 class="flex items-center gap-2 text-base font-semibold text-danger-strong"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>Alertes greenwashing critiques</h2>
                <x-nt.badge variant="danger">{{ $alerts->count() }}</x-nt.badge>
            </x-slot:header>
            <ul class="divide-y">
                @forelse ($alerts as $alert)
                    <li class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.reports.show', $alert->ref) }}" class="font-medium hover:text-primary-strong">{{ $alert->title }}</a>
                            <p class="text-xs text-muted-foreground">{{ $alert->ref }} · {{ $alert->target->name }} · ouvert depuis {{ $alert->open_days }} j</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <x-status-badge type="priority" :value="$alert->priority" />
                            <x-status-badge type="report" :value="$alert->status" />
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-muted-foreground">Aucune alerte critique.</li>
                @endforelse
            </ul>
        </x-nt.card>
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Actions rapides</h2></x-slot:header>
            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['Produit', 'fa-basket-shopping', 'admin.products.create'],
                    ['Lot', 'fa-boxes-stacked', 'admin.batches.create'],
                    ['Acteur', 'fa-people-group', 'admin.actors.create'],
                    ['Empreinte', 'fa-leaf', 'admin.impacts.create'],
                    ['Vérifier', 'fa-file-circle-check', 'admin.certifications.verifications'],
                    ['Modérer', 'fa-gavel', 'admin.reviews.index'],
                ] as [$label, $icon, $route])
                    <a href="{{ route($route) }}" class="flex flex-col items-center gap-2 rounded-lg border p-4 text-center text-sm font-semibold transition hover:border-primary hover:bg-primary/6 hover:text-primary-strong">
                        <i class="fa-solid {{ $icon }} text-lg" aria-hidden="true"></i>{{ $label }}
                    </a>
                @endforeach
            </div>
        </x-nt.card>
    </div>

    {{-- Tables --}}
    <div class="mt-6 grid gap-6 2xl:grid-cols-2">
        <x-nt.data-table title="Derniers signalements" caption="Derniers signalements reçus">
            <x-slot:toolbar><a href="{{ route('admin.reports.index') }}" class="nt-link text-sm">Tout voir</a></x-slot:toolbar>
            <thead><tr><th scope="col">Référence</th><th scope="col">Cible</th><th scope="col">Priorité</th><th scope="col">Statut</th></tr></thead>
            <tbody>
                @foreach ($latestReports as $report)
                    <tr>
                        <td><a href="{{ route('admin.reports.show', $report->ref) }}" class="nt-link font-mono text-xs">{{ $report->ref }}</a><p class="max-w-56 truncate text-xs text-muted-foreground">{{ $report->title }}</p></td>
                        <td class="max-w-40 truncate">{{ $report->target->name }}</td>
                        <td><x-status-badge type="priority" :value="$report->priority" /></td>
                        <td><x-status-badge type="report" :value="$report->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-nt.data-table>

        <x-nt.data-table title="Avis à modérer" caption="Avis en attente de modération">
            <x-slot:toolbar><a href="{{ route('admin.reviews.index', ['statut' => 'pending']) }}" class="nt-link text-sm">Tout voir</a></x-slot:toolbar>
            <thead><tr><th scope="col">Avis</th><th scope="col">Note</th><th scope="col">Statut</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($pendingReviews as $review)
                    <tr>
                        <td><a href="{{ route('admin.reviews.show', $review->id) }}" class="font-medium hover:text-primary-strong">{{ \Illuminate\Support\Str::limit($review->title, 30) }}</a><p class="text-xs text-muted-foreground">{{ $review->user->name }} · {{ $review->product->name }}</p></td>
                        <td><x-nt.rating-stars :rating="$review->rating" size="xs" /></td>
                        <td><x-status-badge type="review" :value="$review->status" /></td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @foreach (['published' => ['fa-check', 'text-primary-strong', 'Approuver'], 'rejected' => ['fa-xmark', 'text-danger-strong', 'Rejeter']] as $status => [$icon, $color, $label])
                                    <form method="POST" action="{{ route('admin.reviews.moderate', $review->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button type="submit" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm {{ $color }}" aria-label="{{ $label }} l'avis « {{ $review->title }} »" title="{{ $label }}">
                                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-nt.data-table>
    </div>
@endsection
