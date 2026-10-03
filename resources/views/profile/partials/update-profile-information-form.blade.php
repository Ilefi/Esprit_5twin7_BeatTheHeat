<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-semibold">Informations du profil</h2>
        <p class="mt-1 text-sm text-muted-foreground">Mettez à jour votre nom et votre adresse e-mail.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <x-nt.form.input name="name" label="Nom complet" :value="$user->name" required autofocus autocomplete="name" />

        <div>
            <x-nt.form.input name="email" type="email" label="Adresse e-mail" :value="$user->email" required autocomplete="username" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-sm">
                    Votre adresse e-mail n'est pas vérifiée.
                    <button form="send-verification" class="nt-link">Renvoyer l'e-mail de vérification</button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-primary-strong">Un nouveau lien de vérification vient d'être envoyé.</p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-nt.button type="submit" icon="fa-check">Enregistrer</x-nt.button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-primary-strong" role="status"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Enregistré.</p>
            @endif
        </div>
    </form>
</section>
