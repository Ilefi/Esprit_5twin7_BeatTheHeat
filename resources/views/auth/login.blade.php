@extends('layouts.auth')

@section('title', 'Connexion')
@section('auth_title', 'Bon retour parmi nous')
@section('auth_subtitle', 'Connectez-vous pour noter vos produits, suivre vos signalements et gérer votre espace.')

@section('content')
    @if (session('status'))
        <x-nt.alert type="success" class="mb-5">{{ session('status') }}</x-nt.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-nt.form.input name="email" type="email" label="Adresse e-mail" icon="fa-envelope" required autofocus autocomplete="username" />
        <x-nt.form.input name="password" type="password" label="Mot de passe" icon="fa-lock" required autocomplete="current-password" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-nt.form.checkbox name="remember" id="remember_me" label="Se souvenir de moi" />
            @if (Route::has('password.request'))
                <a class="nt-link text-sm" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
        </div>

        <x-nt.button type="submit" size="lg" class="w-full" icon="fa-right-to-bracket">Se connecter</x-nt.button>
    </form>

    <div class="mt-6 rounded-lg bg-muted/60 p-4 text-xs text-muted-foreground">
        <p class="font-semibold text-foreground"><i class="fa-solid fa-flask me-1" aria-hidden="true"></i> Comptes de démonstration</p>
        <p class="mt-1">admin@nutritrace.tn · actor@nutritrace.tn · consumer@nutritrace.tn — mot de passe : <code class="font-mono">password</code></p>
    </div>
@endsection

@section('auth_footer')
    Pas encore de compte ? <a href="{{ route('register') }}" class="nt-link">Créer un compte</a>
@endsection
