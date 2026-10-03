@extends('layouts.admin')

@section('title', 'Modifier '.$actor->name)
@section('page_title', 'Modifier l\'acteur')
@section('page_subtitle', $actor->name)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Acteurs', 'url' => route('admin.actors.index')], ['label' => $actor->name]]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('front.actors.show', $actor->slug)" variant="ghost" size="sm" icon="fa-arrow-up-right-from-square">Profil public</x-nt.button>
@endsection

@section('content')
    @include('admin.actors._form', ['actor' => $actor])
@endsection
