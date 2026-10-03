@extends('layouts.master')

@section('title', 'Page introuvable')

@section('body')
    @include('partials.shared.error', [
        'code' => 404,
        'icon' => 'fa-route',
        'title' => 'Ce produit s\'est perdu dans la chaîne…',
        'message' => 'La page demandée n\'existe pas ou a été déplacée. Vérifiez l\'adresse, ou reprenez la traçabilité depuis le début.',
        'actions' => [
            ['Retour à l\'accueil', url('/'), 'fa-house', 'primary'],
            ['Tracer un lot', route('front.traceability.index'), 'fa-qrcode', 'outline'],
        ],
    ])
@endsection
