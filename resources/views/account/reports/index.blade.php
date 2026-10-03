@extends('layouts.account')

@section('title', 'Mes signalements')
@section('account_breadcrumb', 'Mes signalements')
@section('account_title', 'Mes signalements')
@section('account_subtitle', 'Suivez le traitement de chacun de vos signalements.')

@section('account_actions')
    <x-nt.button :href="route('front.reports.create')" variant="danger" size="sm" icon="fa-flag">Nouveau signalement</x-nt.button>
@endsection

@section('account_content')
    <nav aria-label="Filtrer par statut" class="relative -mx-4 mb-6 overflow-x-auto px-4">
        <ul class="flex min-w-max gap-2">
            <li><a href="{{ route('account.reports.index') }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => ! request('statut'), 'nt-btn-outline' => request('statut')])>Tous ({{ $total }})</a></li>
            @foreach ($statuses as $value => $label)
                <li>
                    <a href="{{ route('account.reports.index', ['statut' => $value]) }}" @if (request('statut') === $value) aria-current="true" @endif
                       @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => request('statut') === $value, 'nt-btn-outline' => request('statut') !== $value])>
                        {{ $label }} ({{ $counts[$value] ?? 0 }})
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($reports as $report)
            <x-nt.report-card :report="$report" :href="route('account.reports.show', $report->ref)" />
        @empty
            <x-nt.empty-state icon="fa-flag" title="Aucun signalement pour ce filtre" class="md:col-span-2">
                <x-nt.button :href="route('front.reports.create')" variant="danger" icon="fa-flag">Faire un signalement</x-nt.button>
            </x-nt.empty-state>
        @endforelse
    </div>
@endsection
