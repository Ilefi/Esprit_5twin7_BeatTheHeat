@extends('layouts.auth')

@section('title', 'Inscription')
@section('auth_title', 'Créer un compte')
@section('auth_subtitle', 'Rejoignez la communauté : tracez, notez et signalez en toute transparence.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-nt.form.input name="name" label="Nom complet" icon="fa-user" required autofocus autocomplete="name" />
        <x-nt.form.input name="email" type="email" label="Adresse e-mail" icon="fa-envelope" required autocomplete="username" />
        <x-nt.form.input name="password" type="password" label="Mot de passe" icon="fa-lock" required autocomplete="new-password" hint="8 caractères minimum." />
        <x-nt.form.input name="password_confirmation" type="password" label="Confirmer le mot de passe" icon="fa-lock" required autocomplete="new-password" />

        <x-nt.button type="submit" size="lg" class="w-full" icon="fa-user-plus">Créer mon compte</x-nt.button>
    </form>
@endsection

@section('auth_footer')
    Déjà inscrit ? <a href="{{ route('login') }}" class="nt-link">Se connecter</a>
@endsection
