@extends('layouts.admin')

@section('title', 'Nouveau lot')
@section('page_title', 'Nouveau lot')
@section('page_subtitle', 'Créez le lot, puis ajoutez ses étapes depuis sa fiche.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Lots', 'url' => route('admin.batches.index')], ['label' => 'Nouveau']]])
@endsection

@section('content')
    @include('admin.batches._form', ['batch' => null])
@endsection
