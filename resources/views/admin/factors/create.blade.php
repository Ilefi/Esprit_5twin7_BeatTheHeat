@extends('layouts.admin')

@section('title', 'Nouveau facteur d\'émission')
@section('page_title', 'Créer un facteur d\'émission')
@section('page_subtitle', 'Ajoutez un nouveau coefficient de référence pour le calcul d\'impact environnemental.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', [
        'items' => [
            ['label' => 'Tableau de bord', 'url' => route('admin.dashboard')],
            ['label' => 'Facteurs d\'émission', 'url' => route('admin.factors.index')],
            ['label' => 'Nouveau']
        ]
    ])
@endsection

@section('content')
    @include('admin.factors._form', ['factor' => null])
@endsection
