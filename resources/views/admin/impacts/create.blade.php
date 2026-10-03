@extends('layouts.admin')

@section('title', 'Nouvelle empreinte')
@section('page_title', 'Nouvelle empreinte')
@section('page_subtitle', 'L\'éco-score se met à jour pendant la saisie.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Empreintes', 'url' => route('admin.impacts.index')], ['label' => 'Nouvelle']]])
@endsection

@section('content')
    @include('admin.impacts._form', ['product' => null])
@endsection
