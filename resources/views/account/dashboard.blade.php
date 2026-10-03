@extends('layouts.account')

@section('title', 'Mon espace')
@section('account_title', 'Bonjour '.\Illuminate\Support\Str::before(auth()->user()->name.' ', ' '))
@section('account_subtitle', 'Retrouvez vos avis, suivez vos signalements et découvrez des produits à faible impact.')

@section('account_actions')
    <x-nt.button :href="route('front.products.index')" variant="outline" size="sm" icon="fa-basket-shopping">Explorer les produits</x-nt.button>
@endsection

@section('account_content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <x-nt.stat-card :icon="$stat['icon']" :value="$stat['value']" :label="$stat['label']" :tone="$stat['tone']" />
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <x-nt.card>
            <x-slot:header><h2 class="text-base font-semibold">Activité récente</h2></x-slot:header>
            @if ($activity->isNotEmpty())
                <x-nt.timeline>
                    @foreach ($activity as $item)
                        <x-nt.timeline-item :icon="$item->icon" :tone="$item->tone" :title="$item->title" :time="$item->at->diffForHumans()">
                            <a href="{{ $item->url }}" class="nt-link text-sm">Voir le détail</a>
                        </x-nt.timeline-item>
                    @endforeach
                </x-nt.timeline>
            @else
                <x-nt.empty-state icon="fa-clock-rotate-left" title="Aucune activité" description="Vos avis et signalements apparaîtront ici." />
            @endif
        </x-nt.card>

        <div class="space-y-6">
            <x-nt.card>
                <x-slot:header>
                    <h2 class="text-base font-semibold">Signalements en cours</h2>
                    <a href="{{ route('account.reports.index') }}" class="nt-link text-sm">Tout voir</a>
                </x-slot:header>
                <ul class="space-y-3">
                    @forelse ($openReports as $report)
                        <li>
                            <a href="{{ route('account.reports.show', $report->ref) }}" class="flex items-center gap-3 rounded-lg border p-3 transition hover:border-primary/40 hover:bg-muted/40">
                                <span class="min-w-0 flex-1">
                                    <span class="block font-mono text-xs text-muted-foreground">{{ $report->ref }}</span>
                                    <span class="block truncate text-sm font-medium">{{ $report->title }}</span>
                                </span>
                                <x-status-badge type="report" :value="$report->status" />
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">Aucun signalement en cours.</li>
                    @endforelse
                </ul>
            </x-nt.card>

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Suggestions éco-responsables</h2></x-slot:header>
                <ul class="space-y-3">
                    @foreach ($suggestions as $product)
                        <li class="flex items-center gap-3">
                            <x-nt.product-image :product="$product" size="sm" class="h-12 w-12 shrink-0 rounded-lg" />
                            <a href="{{ route('front.products.show', $product->slug) }}" class="min-w-0 flex-1 truncate text-sm font-medium hover:text-primary-strong">{{ $product->name }}</a>
                            <x-nt.eco-score :grade="$product->eco_score" size="sm" />
                        </li>
                    @endforeach
                </ul>
            </x-nt.card>
        </div>
    </div>
@endsection
