@extends('layouts.master')

@section('title', 'Session expirée')

@section('body')
    @include('partials.shared.error', [
        'code' => 419,
        'icon' => 'fa-hourglass-end',
        'title' => 'La récolte a trop attendu',
        'message' => 'Votre session a expiré pour des raisons de sécurité. Rechargez la page puis renvoyez le formulaire.',
        'actions' => [
            ['Recharger la page', url()->previous(), 'fa-rotate-right', 'primary'],
            ['Retour à l\'accueil', url('/'), 'fa-house', 'outline'],
        ],
    ])
@endsection
