@extends('layouts.admin')

@section('title', 'Modifier '.$certification->name)
@section('page_title', 'Modifier la certification')
@section('page_subtitle', $certification->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Certifications', 'url' => route('admin.certifications.index')], ['label' => $certification->name]]])
@endsection

@section('content')
    @include('admin.certifications._form', ['certification' => $certification])
@endsection
