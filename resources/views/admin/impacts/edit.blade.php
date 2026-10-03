@extends('layouts.admin')

@section('title', 'Empreinte — '.$product->name)
@section('page_title', 'Modifier l\'empreinte')
@section('page_subtitle', $product->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Empreintes', 'url' => route('admin.impacts.index')], ['label' => $product->name]]])
@endsection

@section('content')
    @include('admin.impacts._form', ['product' => $product])
@endsection
