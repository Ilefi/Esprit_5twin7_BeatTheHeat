@extends('layouts.admin')

@section('title', 'Nouvelle certification')
@section('page_title', 'Nouvelle certification')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Certifications', 'url' => route('admin.certifications.index')], ['label' => 'Nouvelle']]])
@endsection

@section('content')
    @include('admin.certifications._form', ['certification' => null])
@endsection
