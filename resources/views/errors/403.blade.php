@extends('layouts.master')

@section('title', 'Accès refusé')

@section('body')
    @include('partials.shared.error', [
        'code' => 403,
        'icon' => 'fa-lock',
        'title' => 'Cette parcelle est clôturée',
        'message' => 'Vous n\'avez pas les droits nécessaires pour accéder à cette page. Si vous pensez qu\'il s\'agit d\'une erreur, contactez un administrateur.',
        'actions' => [
            ['Retour à l\'accueil', url('/'), 'fa-house', 'primary'],
            ['Mon espace', route('account.dashboard'), 'fa-gauge', 'outline'],
        ],
    ])
@endsection
