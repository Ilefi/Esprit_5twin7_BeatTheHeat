@extends('layouts.admin')

@section('title', 'Catégories')
@section('page_title', 'Catégories de produits')
@section('page_subtitle', 'Création et modification rapides dans une fenêtre.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Produits', 'url' => route('admin.products.index')], ['label' => 'Catégories']]])
@endsection

@section('page_actions')
    <x-nt.button size="sm" icon="fa-plus" x-data x-on:click="$dispatch('open-modal', 'create-category')">Nouvelle catégorie</x-nt.button>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($categories as $category)
            <div class="nt-card flex items-center gap-4 p-5">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-primary/12 text-lg text-primary-strong"><i class="fa-solid {{ $category->icon }}" aria-hidden="true"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold">{{ $category->name }}</p>
                    <p class="text-sm text-muted-foreground">{{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }}</p>
                </div>
                <div class="flex gap-1">
                    <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" x-data x-on:click="$dispatch('open-modal', 'edit-category-{{ $category->id }}')" aria-label="Modifier {{ $category->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                    <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')" aria-label="Supprimer {{ $category->name }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </div>
            </div>

            @php $bag = 'editCategory'.$category->id; @endphp
            <x-nt.modal :name="'edit-category-'.$category->id" title="Modifier la catégorie" :show="$errors->{$bag}->isNotEmpty()">
                <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" id="edit-category-form-{{ $category->id }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <x-nt.form.input name="name" label="Nom" :value="$category->name" :bag="$bag" :use-old="$errors->{$bag}->isNotEmpty()" :id="'category-name-'.$category->id" required />
                    <x-nt.form.select name="icon" label="Icône" :options="$icons" :value="$category->icon" :bag="$bag" :use-old="$errors->{$bag}->isNotEmpty()" :id="'category-icon-'.$category->id" required />
                </form>
                <x-slot:footer>
                    <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                    <x-nt.button type="submit" form="edit-category-form-{{ $category->id }}" icon="fa-check">Enregistrer</x-nt.button>
                </x-slot:footer>
            </x-nt.modal>

            @include('partials.admin.delete-modal', ['name' => 'delete-category-'.$category->id, 'action' => route('admin.categories.destroy', $category->id), 'label' => $category->name, 'warning' => 'Les produits associés devront être reclassés.'])
        @endforeach
    </div>

    <x-nt.modal name="create-category" title="Nouvelle catégorie" :show="$errors->createCategory->isNotEmpty()">
        <form method="POST" action="{{ route('admin.categories.store') }}" id="create-category-form" class="space-y-5">
            @csrf
            <x-nt.form.input name="name" label="Nom" bag="createCategory" id="new-category-name" required />
            <x-nt.form.select name="icon" label="Icône" :options="$icons" bag="createCategory" id="new-category-icon" placeholder="Choisir une icône" required />
        </form>
        <x-slot:footer>
            <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
            <x-nt.button type="submit" form="create-category-form" icon="fa-plus">Créer</x-nt.button>
        </x-slot:footer>
    </x-nt.modal>
@endsection
