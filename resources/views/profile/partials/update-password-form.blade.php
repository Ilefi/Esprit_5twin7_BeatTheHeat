<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-semibold">Mot de passe</h2>
        <p class="mt-1 text-sm text-muted-foreground">Utilisez un mot de passe long et unique pour sécuriser votre compte.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <x-nt.form.input name="current_password" id="update_password_current_password" type="password" label="Mot de passe actuel" bag="updatePassword" autocomplete="current-password" />
        <x-nt.form.input name="password" id="update_password_password" type="password" label="Nouveau mot de passe" bag="updatePassword" autocomplete="new-password" />
        <x-nt.form.input name="password_confirmation" id="update_password_password_confirmation" type="password" label="Confirmer le mot de passe" bag="updatePassword" autocomplete="new-password" />

        <div class="flex items-center gap-4">
            <x-nt.button type="submit" icon="fa-key">Mettre à jour</x-nt.button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-primary-strong" role="status"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Mot de passe mis à jour.</p>
            @endif
        </div>
    </form>
</section>
