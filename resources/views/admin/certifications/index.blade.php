@extends('layouts.admin')

@section('title', 'Certifications')
@section('page_title', 'Certifications')
@section('page_subtitle', 'Référentiel des labels reconnus sur la plateforme.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Certifications']]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('admin.certifications.verifications')" variant="outline" size="sm" icon="fa-file-circle-check">
        Vérifications <span class="nt-badge nt-badge-gold px-2 py-0">{{ $pendingCount }}</span>
    </x-nt.button>
    <x-nt.button :href="route('admin.certifications.create')" size="sm" icon="fa-plus">Nouvelle certification</x-nt.button>
@endsection

@section('content')
    <x-nt.data-table caption="Liste des certifications">
        <thead><tr><th scope="col">Label</th><th scope="col">Organisme</th><th scope="col">Expiration</th><th scope="col">Produits</th><th scope="col">Acteurs</th><th scope="col" class="text-right">Actions</th></tr></thead>
        <tbody>
            @foreach ($certifications as $certification)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-nt.cert-badge :certification="$certification" />
                            <span class="font-semibold">{{ $certification->name }}</span>
                        </div>
                    </td>
                    <td class="max-w-64 truncate text-muted-foreground">{{ $certification->issuer }}</td>
                    <td class="whitespace-nowrap">
                        @if ($certification->is_expired)
                            <x-nt.badge variant="danger" icon="fa-calendar-xmark">Expirée le {{ $certification->expires_at->translatedFormat('d M Y') }}</x-nt.badge>
                        @else
                            <span class="text-muted-foreground">{{ $certification->expires_at?->translatedFormat('d M Y') ?? 'Sans expiration' }}</span>
                        @endif
                    </td>
                    <td>{{ $certification->products_count }}</td>
                    <td>{{ $certification->actors_count }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('front.certifications.show', $certification->slug) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Voir {{ $certification->name }} sur le site"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.certifications.edit', $certification->id) }}" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="Modifier {{ $certification->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-cert-{{ $certification->id }}')" aria-label="Supprimer {{ $certification->name }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-nt.data-table>

    @foreach ($certifications as $certification)
        @include('partials.admin.delete-modal', ['name' => 'delete-cert-'.$certification->id, 'action' => route('admin.certifications.destroy', $certification->id), 'label' => $certification->name, 'warning' => 'Le label sera retiré des produits et acteurs qui l\'affichent.'])
    @endforeach
@endsection
