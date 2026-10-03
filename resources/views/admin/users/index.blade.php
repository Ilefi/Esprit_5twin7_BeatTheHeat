@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Utilisateurs')
@section('page_subtitle', 'Comptes de la plateforme et rôles associés.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Utilisateurs']]])
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ($roles as $value => $label)
            <x-nt.stat-card :icon="\App\View\Components\StatusBadge::iconFor('role', $value)" :value="$counts[$value] ?? 0" :label="$label.'s'"
                            :tone="['admin' => 'earth', 'actor' => 'gold', 'consumer' => 'primary'][$value]" :href="route('admin.users.index', ['role' => $value])" />
        @endforeach
    </div>

    @include('partials.admin.filters', ['action' => route('admin.users.index'), 'search' => 'Nom ou e-mail…', 'selects' => ['role' => ['Rôle', $roles]]])

    <x-nt.data-table caption="Liste des utilisateurs" class="mt-6">
        <thead><tr><th scope="col">Utilisateur</th><th scope="col">Rôle</th><th scope="col">Avis</th><th scope="col">Signalements</th><th scope="col">Inscrit</th><th scope="col">Dernière connexion</th><th scope="col" class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-nt.avatar :name="$user->name" size="sm" />
                            <div><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-muted-foreground">{{ $user->email }}</p></div>
                        </div>
                    </td>
                    <td><x-status-badge type="role" :value="$user->role" /></td>
                    <td>{{ $user->reviews_count }}</td>
                    <td>{{ $user->reports_count }}</td>
                    <td class="whitespace-nowrap text-xs text-muted-foreground">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                    <td class="whitespace-nowrap text-xs text-muted-foreground">{{ $user->last_login_at->diffForHumans() }}</td>
                    <td><div class="flex justify-end"><a href="{{ route('admin.users.edit', $user->id) }}" class="nt-btn nt-btn-outline nt-btn-sm" aria-label="Modifier le rôle de {{ $user->name }}"><i class="fa-solid fa-user-pen" aria-hidden="true"></i> Rôle</a></div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-10 text-center text-muted-foreground">Aucun utilisateur ne correspond.</td></tr>
            @endforelse
        </tbody>
        <x-slot:footer>{{ $users->links() }}</x-slot:footer>
    </x-nt.data-table>
@endsection
