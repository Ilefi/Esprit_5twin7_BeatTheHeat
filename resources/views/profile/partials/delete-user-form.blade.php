<section class="max-w-xl space-y-5">
    <header>
        <h2 class="text-lg font-semibold text-danger-strong">Supprimer le compte</h2>
        <p class="mt-1 text-sm text-muted-foreground">La suppression est définitive : vos données, avis et signalements seront effacés. Téléchargez d'abord ce que vous souhaitez conserver.</p>
    </header>

    <x-nt.button variant="danger" icon="fa-trash" x-data x-on:click="$dispatch('open-modal', 'confirm-user-deletion')">Supprimer mon compte</x-nt.button>

    <x-nt.modal name="confirm-user-deletion" title="Supprimer définitivement votre compte ?" :show="$errors->userDeletion->isNotEmpty()" max-width="md">
        <form method="post" action="{{ route('profile.destroy') }}" id="delete-user-form" class="space-y-4">
            @csrf
            @method('delete')

            <p class="text-sm text-muted-foreground">Saisissez votre mot de passe pour confirmer la suppression définitive de votre compte.</p>

            <x-nt.form.input name="password" id="delete_user_password" type="password" label="Mot de passe" bag="userDeletion" placeholder="Mot de passe" />
        </form>

        <x-slot:footer>
            <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
            <x-nt.button type="submit" form="delete-user-form" variant="danger" icon="fa-trash">Supprimer le compte</x-nt.button>
        </x-slot:footer>
    </x-nt.modal>
</section>
