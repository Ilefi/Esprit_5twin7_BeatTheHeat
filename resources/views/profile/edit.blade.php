@extends('layouts.account')

@section('title', 'Mon profil')
@section('account_breadcrumb', 'Mon profil')
@section('account_title', 'Mon profil')
@section('account_subtitle', 'Gérez vos informations personnelles, votre mot de passe et votre compte.')

@section('account_content')
    <div class="space-y-6">
        <x-nt.card>
            @include('profile.partials.update-profile-information-form')
        </x-nt.card>

        <x-nt.card>
            @include('profile.partials.update-password-form')
        </x-nt.card>

        <x-nt.card class="border-danger/30">
            @include('profile.partials.delete-user-form')
        </x-nt.card>
    </div>
@endsection
