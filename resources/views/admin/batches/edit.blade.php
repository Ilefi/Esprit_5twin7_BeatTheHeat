@extends('layouts.admin')

@section('title', 'Modifier le lot '.$batch->code)
@section('page_title', 'Modifier le lot')
@section('page_subtitle', $batch->code.' · '.$batch->product->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Lots', 'url' => route('admin.batches.index')], ['label' => $batch->code, 'url' => route('admin.batches.show', $batch->id)], ['label' => 'Modifier']]])
@endsection

@section('content')
    @include('admin.batches._form', ['batch' => $batch])
@endsection
