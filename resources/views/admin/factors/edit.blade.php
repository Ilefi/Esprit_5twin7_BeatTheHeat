@extends('layouts.admin')

@section('title', 'Modifier le facteur d\'émission')
@section('page_title', 'Modifier : ' . $factor->name)
@section('page_subtitle', 'Mise à jour des valeurs ou sources de référence.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', [
        'items' => [
            ['label' => 'Tableau de bord', 'url' => route('admin.dashboard')],
            ['label' => 'Facteurs d\'émission', 'url' => route('admin.factors.index')],
            ['label' => 'Modifier']
        ]
    ])
@endsection

@section('content')
    @include('admin.factors._form', ['factor' => $factor])
@endsection
