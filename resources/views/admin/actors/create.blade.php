@extends('layouts.admin')

@section('title', 'Nouvel acteur')
@section('page_title', 'Nouvel acteur')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Acteurs', 'url' => route('admin.actors.index')], ['label' => 'Nouveau']]])
@endsection

@section('content')
    @include('admin.actors._form', ['actor' => null])
@endsection
