{{-- @include('partials.admin.delete-modal', ['name' => 'delete-product-1', 'action' => route(...), 'label' => 'Huile d\'olive']) --}}
<x-nt.modal :name="$name" title="Confirmer la suppression" max-width="md">
    <div class="flex gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-danger/10 text-lg text-danger-strong"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
        <p class="text-sm text-muted-foreground">
            Voulez-vous vraiment supprimer <strong class="text-foreground">{{ $label }}</strong> ?
            {{ $warning ?? 'Cette action est irréversible.' }}
        </p>
    </div>
    <x-slot:footer>
        <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
        <form method="POST" action="{{ $action }}">
            @csrf
            @method('DELETE')
            <x-nt.button type="submit" variant="danger" icon="fa-trash">Supprimer</x-nt.button>
        </form>
    </x-slot:footer>
</x-nt.modal>
