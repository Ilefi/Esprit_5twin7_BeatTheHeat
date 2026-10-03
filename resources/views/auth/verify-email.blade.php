@extends('layouts.auth')

@section('title', 'Vérification de l\'e-mail')
@section('auth_title', 'Vérifiez votre adresse e-mail')
@section('auth_subtitle', 'Merci pour votre inscription ! Cliquez sur le lien que nous venons de vous envoyer. Rien reçu ? Nous pouvons vous en renvoyer un.')

@section('content')
    @if (session('status') == 'verification-link-sent')
        <x-nt.alert type="success" class="mb-5">Un nouveau lien de vérification a été envoyé à l'adresse indiquée lors de l'inscription.</x-nt.alert>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-nt.button type="submit" icon="fa-paper-plane">Renvoyer l'e-mail</x-nt.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-nt.button type="submit" variant="ghost" icon="fa-right-from-bracket">Se déconnecter</x-nt.button>
        </form>
    </div>
@endsection
