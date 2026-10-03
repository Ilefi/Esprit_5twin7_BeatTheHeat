@extends('layouts.admin')

@section('title', 'Rôle de '.$user->name)
@section('page_title', 'Modifier le rôle')
@section('page_subtitle', $user->name.' · '.$user->email)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Utilisateurs', 'url' => route('admin.users.index')], ['label' => $user->name]]])
@endsection

@section('content')
    <x-nt.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                <x-nt.avatar :name="$user->name" size="lg" />
                <div>
                    <p class="font-heading text-lg font-semibold">{{ $user->name }}</p>
                    <p class="text-sm text-muted-foreground">Inscrit le {{ $user->created_at->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <fieldset>
                <legend class="nt-label mb-3">Rôle</legend>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        'consumer' => 'Note les produits, signale, suit ses dossiers.',
                        'actor' => 'Gère ses produits, lots et certificats.',
                        'admin' => 'Accès complet au back office.',
                    ] as $value => $description)
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="{{ $value }}" class="peer sr-only" @checked(old('role', $user->role) === $value)>
                            <span class="flex h-full flex-col gap-2 rounded-xl border-2 p-4 transition peer-checked:border-primary peer-checked:bg-primary/6 peer-focus-visible:ring-2 peer-focus-visible:ring-ring">
                                <x-status-badge type="role" :value="$value" />
                                <span class="text-sm text-muted-foreground">{{ $description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('role')<p class="mt-2 text-sm text-danger-strong">{{ $message }}</p>@enderror
            </fieldset>

            <x-nt.alert type="warning">Le rôle administrateur donne accès à toutes les données de modération.</x-nt.alert>

            <div class="flex justify-end gap-3">
                <x-nt.button :href="route('admin.users.index')" variant="ghost">Annuler</x-nt.button>
                <x-nt.button type="submit" icon="fa-check">Enregistrer</x-nt.button>
            </div>
        </form>
    </x-nt.card>
@endsection
