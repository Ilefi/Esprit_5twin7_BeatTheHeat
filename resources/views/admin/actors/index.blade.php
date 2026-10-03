@extends('layouts.admin')

@section('title', 'Acteurs')
@section('page_title', 'Acteurs de la chaîne')
@section('page_subtitle', 'Producteurs, transformateurs et distributeurs.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Acteurs']]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.actors.create')" size="sm" icon="fa-plus">Nouvel acteur</x-nt.button>
@endsection

@section('content')
    @include('partials.admin.filters', ['action' => route('admin.actors.index'), 'search' => 'Nom ou ville…', 'selects' => ['type' => ['Type', $types]]])

    <x-nt.data-table caption="Liste des acteurs" class="mt-6">
        <thead><tr><th scope="col">Acteur</th><th scope="col">Type</th><th scope="col">Localisation</th><th scope="col">Certifications</th><th scope="col">Lots</th><th scope="col" class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse ($actors as $actor)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-nt.avatar :name="$actor->name" size="sm" />
                            <div>
                                <p class="font-semibold">{{ $actor->name }} @if ($actor->verified)<i class="fa-solid fa-circle-check ms-1 text-primary" aria-label="Vérifié"></i>@endif</p>
                                <p class="text-xs text-muted-foreground">{{ $actor->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td><x-status-badge type="actor_type" :value="$actor->type" /></td>
                    <td class="whitespace-nowrap">{{ $actor->city }}, {{ $actor->region }}</td>
                    <td><div class="flex flex-wrap gap-1">@forelse ($actor->certifications as $certification)<x-nt.cert-badge :certification="$certification" />@empty<span class="text-xs text-muted-foreground">—</span>@endforelse</div></td>
                    <td>{{ $actor->batches_count }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('front.actors.show', $actor->slug) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Profil public de {{ $actor->name }}"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.actors.edit', $actor->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Modifier {{ $actor->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-actor-{{ $actor->id }}')" aria-label="Supprimer {{ $actor->name }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-muted-foreground">Aucun acteur ne correspond.</td></tr>
            @endforelse
        </tbody>
        <x-slot:footer>{{ $actors->links() }}</x-slot:footer>
    </x-nt.data-table>

    @foreach ($actors as $actor)
        @include('partials.admin.delete-modal', ['name' => 'delete-actor-'.$actor->id, 'action' => route('admin.actors.destroy', $actor->id), 'label' => $actor->name])
    @endforeach
@endsection
