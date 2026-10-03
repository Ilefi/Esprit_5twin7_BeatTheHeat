@extends('layouts.auth')

@section('title', 'Confirmation')
@section('auth_title', 'Zone sécurisée')
@section('auth_subtitle', 'Merci de confirmer votre mot de passe avant de continuer.')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-nt.form.input name="password" type="password" label="Mot de passe" icon="fa-lock" required autocomplete="current-password" />

        <x-nt.button type="submit" size="lg" class="w-full" icon="fa-shield-halved">Confirmer</x-nt.button>
    </form>
@endsection
