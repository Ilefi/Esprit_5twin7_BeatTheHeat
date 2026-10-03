@extends('layouts.master')

@section('title', 'Maintenance en cours')

@section('body')
    @include('partials.shared.error', [
        'code' => 503,
        'icon' => 'fa-seedling',
        'title' => 'Nous préparons la prochaine saison',
        'message' => 'NutriTrace est en maintenance pour quelques minutes. Merci de votre patience, revenez très vite !',
        'actions' => [],
    ])
@endsection
