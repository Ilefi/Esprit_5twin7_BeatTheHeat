@extends('layouts.master')

@section('title', 'Erreur serveur')

@section('body')
    @include('partials.shared.error', [
        'code' => 500,
        'icon' => 'fa-screwdriver-wrench',
        'title' => 'Un grain de sable dans l\'engrenage',
        'message' => 'Une erreur inattendue est survenue de notre côté. Notre équipe a été prévenue ; réessayez dans quelques instants.',
    ])
@endsection
