@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')
@section('auth_title', 'Choisissez un nouveau mot de passe')

@section('content')
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-nt.form.input name="email" type="email" label="Adresse e-mail" icon="fa-envelope" :value="$request->email" required autofocus autocomplete="username" />
        <x-nt.form.input name="password" type="password" label="Nouveau mot de passe" icon="fa-lock" required autocomplete="new-password" />
        <x-nt.form.input name="password_confirmation" type="password" label="Confirmer le mot de passe" icon="fa-lock" required autocomplete="new-password" />

        <x-nt.button type="submit" size="lg" class="w-full" icon="fa-key">Réinitialiser le mot de passe</x-nt.button>
    </form>
@endsection
