@extends('layouts.admin')

@section('title', 'Modifier '.$product->name)
@section('page_title', 'Modifier le produit')
@section('page_subtitle', $product->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Produits', 'url' => route('admin.products.index')], ['label' => $product->name, 'url' => route('admin.products.show', $product->id)], ['label' => 'Modifier']]])
@endsection

@section('content')
    @include('admin.products._form', ['product' => $product])
@endsection
