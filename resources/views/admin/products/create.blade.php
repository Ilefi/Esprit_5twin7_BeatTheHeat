@extends('layouts.admin')

@section('title', 'Nouveau produit')
@section('page_title', 'Nouveau produit')
@section('page_subtitle', 'Les champs marqués d\'un astérisque sont obligatoires.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Produits', 'url' => route('admin.products.index')], ['label' => 'Nouveau']]])
@endsection

@section('content')
    @include('admin.products._form', ['product' => null])
@endsection
