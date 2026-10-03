@extends('layouts.admin')

@section('title', 'Lots')
@section('page_title', 'Lots tracés')
@section('page_subtitle', 'Chaque lot regroupe les étapes de la ferme au point de vente.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Lots']]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.batches.create')" size="sm" icon="fa-plus">Nouveau lot</x-nt.button>
@endsection

@section('content')
    @include('partials.admin.filters', ['action' => route('admin.batches.index'), 'search' => 'Code ou produit…', 'selects' => ['statut' => ['Statut', $statuses]]])

    <x-nt.data-table caption="Liste des lots" class="mt-6">
        <thead><tr><th scope="col">Lot</th><th scope="col">Produit</th><th scope="col">Progression</th><th scope="col">Distance</th><th scope="col">Statut</th><th scope="col" class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse ($batches as $batch)
                @php $verified = $batch->steps->where('verified', true)->count(); @endphp
                <tr>
                    <td><a href="{{ route('admin.batches.show', $batch->id) }}" class="nt-link font-mono text-xs">{{ $batch->code }}</a><p class="text-xs text-muted-foreground">{{ $batch->production_date->translatedFormat('d M Y') }}</p></td>
                    <td>{{ $batch->product->name }}</td>
                    <td class="min-w-40">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="h-2 flex-1 overflow-hidden rounded-full bg-muted" aria-hidden="true"><span class="block h-full rounded-full bg-primary" style="width: {{ round($verified / max(1, $batch->steps->count()) * 100) }}%"></span></span>
                            <span class="whitespace-nowrap text-muted-foreground">{{ $verified }}/{{ $batch->steps->count() }} vérifiées</span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap">{{ $batch->total_km }} km</td>
                    <td><x-status-badge type="batch" :value="$batch->status" /></td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.batches.show', $batch->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Éditer la chronologie de {{ $batch->code }}"><i class="fa-solid fa-timeline" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.batches.edit', $batch->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Modifier {{ $batch->code }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-batch-{{ $batch->id }}')" aria-label="Supprimer {{ $batch->code }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-muted-foreground">Aucun lot ne correspond.</td></tr>
            @endforelse
        </tbody>
        <x-slot:footer>{{ $batches->links() }}</x-slot:footer>
    </x-nt.data-table>

    @foreach ($batches as $batch)
        @include('partials.admin.delete-modal', ['name' => 'delete-batch-'.$batch->id, 'action' => route('admin.batches.destroy', $batch->id), 'label' => 'le lot '.$batch->code, 'warning' => 'Toutes ses étapes seront supprimées.'])
    @endforeach
@endsection
