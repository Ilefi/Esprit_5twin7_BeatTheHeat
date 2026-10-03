@extends('layouts.admin')

@section('title', 'Signalements')
@section('page_title', 'Signalements')
@section('page_subtitle', 'Triés par priorité puis par ancienneté.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Signalements']]])
@endsection

@section('page_actions')
    <div class="inline-flex rounded-lg border bg-surface p-1" role="group" aria-label="Mode d'affichage">
        <a href="{{ route('admin.reports.index', request()->except(['vue', 'page'])) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => $view === 'table', 'nt-btn-ghost' => $view !== 'table']) @if ($view === 'table') aria-current="true" @endif>
            <i class="fa-solid fa-table-list" aria-hidden="true"></i> Tableau
        </a>
        <a href="{{ route('admin.reports.index', ['vue' => 'kanban'] + request()->except(['vue', 'page'])) }}" @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => $view === 'kanban', 'nt-btn-ghost' => $view !== 'kanban']) @if ($view === 'kanban') aria-current="true" @endif>
            <i class="fa-solid fa-table-columns" aria-hidden="true"></i> Kanban
        </a>
    </div>
@endsection

@section('content')
    @include('partials.admin.filters', [
        'action' => route('admin.reports.index'),
        'selects' => array_filter([
            'type' => ['Type', $types],
            'statut' => $view === 'table' ? ['Statut', $statuses] : null,
            'priorite' => ['Priorité', $priorities],
            'periode' => ['Période', $periods],
        ]),
        'keep' => ['vue'],
    ])

    @if ($view === 'kanban')
        <div class="relative -mx-4 mt-6 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="grid min-w-max auto-cols-[18rem] grid-flow-col gap-4">
                @foreach ($statuses as $status => $label)
                    @php $cards = $columns[$status] ?? collect(); @endphp
                    <section class="flex flex-col rounded-xl bg-muted/60 p-3" aria-labelledby="col-{{ $status }}">
                        <header class="mb-3 flex items-center justify-between px-1">
                            <h2 id="col-{{ $status }}"><x-status-badge type="report" :value="$status" /></h2>
                            <span class="text-sm font-semibold text-muted-foreground">{{ $cards->count() }}</span>
                        </header>
                        <div class="space-y-3">
                            @forelse ($cards as $report)
                                <article class="nt-card p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-xs text-muted-foreground">{{ $report->ref }}</span>
                                        <x-status-badge type="priority" :value="$report->priority" />
                                    </div>
                                    <h3 class="mt-2 text-sm font-semibold leading-snug"><a href="{{ route('admin.reports.show', $report->ref) }}" class="hover:text-primary-strong">{{ $report->title }}</a></h3>
                                    <p class="mt-1 truncate text-xs text-muted-foreground"><i class="fa-solid {{ $report->target->icon }} me-1" aria-hidden="true"></i>{{ $report->target->name }}</p>
                                    <div class="mt-3 flex items-center justify-between gap-2 border-t pt-3">
                                        @include('admin.reports._open-since', ['report' => $report])
                                        @if ($report->assignee)
                                            <x-nt.avatar :name="$report->assignee->name" size="sm" />
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.reports.status', $report->ref) }}" class="mt-3 flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="status-{{ $report->id }}" class="sr-only">Changer le statut de {{ $report->ref }}</label>
                                        <select id="status-{{ $report->id }}" name="status" class="nt-input py-1.5 text-xs">
                                            @foreach ($statuses as $value => $statusLabel)
                                                <option value="{{ $value }}" @selected($report->status === $value)>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="nt-btn nt-btn-outline nt-btn-icon nt-btn-sm" aria-label="Appliquer le statut"><i class="fa-solid fa-check" aria-hidden="true"></i></button>
                                    </form>
                                </article>
                            @empty
                                <p class="rounded-lg border-2 border-dashed px-3 py-6 text-center text-xs text-muted-foreground">Aucun signalement</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    @else
        <x-nt.data-table caption="Liste des signalements" class="mt-6">
            <thead><tr><th scope="col">Signalement</th><th scope="col">Type</th><th scope="col">Cible</th><th scope="col">Priorité</th><th scope="col">Statut</th><th scope="col">Ancienneté</th><th scope="col">Responsable</th></tr></thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td class="max-w-xs">
                            <a href="{{ route('admin.reports.show', $report->ref) }}" class="font-mono text-xs nt-link">{{ $report->ref }}</a>
                            <p class="line-clamp-1 text-sm font-medium">{{ $report->title }}</p>
                        </td>
                        <td><x-status-badge type="report_type" :value="$report->type" /></td>
                        <td class="max-w-40 truncate">{{ $report->target->name }}</td>
                        <td><x-status-badge type="priority" :value="$report->priority" /></td>
                        <td><x-status-badge type="report" :value="$report->status" /></td>
                        <td class="whitespace-nowrap">@include('admin.reports._open-since', ['report' => $report])</td>
                        <td>
                            @if ($report->assignee)
                                <span class="flex items-center gap-2 whitespace-nowrap text-sm"><x-nt.avatar :name="$report->assignee->name" size="sm" />{{ \Illuminate\Support\Str::before($report->assignee->name, ' ') }}</span>
                            @else
                                <span class="nt-badge nt-badge-warning">Non assigné</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-muted-foreground">Aucun signalement ne correspond aux filtres.</td></tr>
                @endforelse
            </tbody>
            <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
        </x-nt.data-table>
    @endif
@endsection
