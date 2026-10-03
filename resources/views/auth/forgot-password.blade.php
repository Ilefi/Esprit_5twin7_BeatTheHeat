@extends('layouts.auth')

@section('title', 'Mot de passe oublié')
@section('auth_title', 'Mot de passe oublié')
@section('auth_subtitle', 'Indiquez votre adresse e-mail : nous vous enverrons un lien pour choisir un nouveau mot de passe.')

@section('content')
    @if (session('status'))
        <x-nt.alert type="success" class="mb-5">{{ session('status') }}</x-nt.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-nt.form.input name="email" type="email" label="Adresse e-mail" icon="fa-envelope" required autofocus />

        <x-nt.button type="submit" size="lg" class="w-full" icon="fa-paper-plane">Envoyer le lien de réinitialisation</x-nt.button>
    </form>
@endsection

@section('auth_footer')
    <a href="{{ route('login') }}" class="nt-link">Retour à la connexion</a>
@endsection
