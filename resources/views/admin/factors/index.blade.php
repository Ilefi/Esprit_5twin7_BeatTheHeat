@extends('layouts.admin')

@section('title', 'Facteurs d\'émission')
@section('page_title', 'Facteurs d\'émission')
@section('page_subtitle', 'Table de référence des facteurs d\'émission.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', [
        'items' => [
            ['label' => 'Tableau de bord', 'url' => route('admin.dashboard')],
            ['label' => 'Empreintes', 'url' => route('admin.impacts.index')],
            ['label' => 'Facteurs d\'émission']
        ]
    ])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.factors.create')" size="sm" icon="fa-plus">Nouveau facteur</x-nt.button>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <!-- Filtres par catégorie -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.factors.index') }}"
               class="nt-badge {{ !$selectedCategory ? 'bg-primary text-primary-foreground font-semibold' : 'bg-muted text-muted-foreground hover:bg-muted/80' }}">
                Tous ({{ $totalCount }})
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('admin.factors.index', ['categorie' => $cat]) }}"
                   class="nt-badge {{ $selectedCategory === $cat ? 'bg-primary text-primary-foreground font-semibold' : 'bg-muted text-muted-foreground hover:bg-muted/80' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Recherche rapide -->
        <form method="GET" action="{{ route('admin.factors.index') }}" class="flex items-center gap-2">
            @if ($selectedCategory)
                <input type="hidden" name="categorie" value="{{ $selectedCategory }}">
            @endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher..."
                   class="nt-input !h-9 text-xs sm:w-56" />
            <button type="submit" class="nt-btn nt-btn-outline nt-btn-sm nt-btn-icon" aria-label="Rechercher">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </button>
            @if ($search || $selectedCategory)
                <a href="{{ route('admin.factors.index') }}" class="nt-btn nt-btn-ghost nt-btn-sm" title="Réinitialiser">
                    <i class="fa-solid fa-xmark text-muted-foreground" aria-hidden="true"></i>
                </a>
            @endif
        </form>
    </div>

    <x-nt.data-table caption="Facteurs d'émission">
        <thead>
            <tr>
                <th scope="col">Facteur</th>
                <th scope="col">Catégorie</th>
                <th scope="col" class="text-right">Valeur</th>
                <th scope="col">Unité</th>
                <th scope="col">Source</th>
                <th scope="col" class="text-center">Impacts liés</th>
                <th scope="col" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($factors as $factor)
                <tr>
                    <td class="font-medium text-foreground">{{ $factor->name }}</td>
                    <td>
                        <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary-strong">
                            {{ $factor->category }}
                        </span>
                    </td>
                    <td class="text-right font-mono font-medium">
                        {{ number_format($factor->value, $factor->value < 1 ? 3 : 2, ',', ' ') }}
                    </td>
                    <td class="whitespace-nowrap text-muted-foreground text-xs">{{ $factor->unit }}</td>
                    <td class="text-xs text-muted-foreground max-w-[200px] truncate" title="{{ $factor->source }}">
                        {{ $factor->source }}
                    </td>
                    <td class="text-center">
                        <span class="nt-badge {{ $factor->impacts_count > 0 ? 'nt-badge-success' : 'nt-badge-neutral' }}">
                            {{ $factor->impacts_count }}
                        </span>
                    </td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.factors.edit', $factor->id) }}"
                               class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm"
                               aria-label="Modifier {{ $factor->name }}">
                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                            </a>
                            <button type="button"
                                    class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong"
                                    x-data
                                    x-on:click="$dispatch('open-modal', 'delete-factor-{{ $factor->id }}')"
                                    aria-label="Supprimer {{ $factor->name }}">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-6 text-center text-muted-foreground">
                        Aucun facteur d'émission trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($factors->hasPages())
            <x-slot:footer>{{ $factors->links() }}</x-slot:footer>
        @endif
    </x-nt.data-table>

    @foreach ($factors as $factor)
        @include('partials.admin.delete-modal', [
            'name' => 'delete-factor-'.$factor->id,
            'action' => route('admin.factors.destroy', $factor->id),
            'label' => 'le facteur d\'émission « '.$factor->name.' »'
        ])
    @endforeach
@endsection
