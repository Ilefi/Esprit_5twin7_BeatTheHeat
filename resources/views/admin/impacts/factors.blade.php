@extends('layouts.admin')

@section('title', 'Facteurs d\'émission')
@section('page_title', 'Facteurs d\'émission')
@section('page_subtitle', 'Table de référence utilisée pour calculer les empreintes.')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Empreintes', 'url' => route('admin.impacts.index')], ['label' => 'Facteurs d\'émission']]])
@endsection

@section('content')
    <x-nt.alert type="info" title="Valeurs pédagogiques" class="mb-6">
        Ces facteurs sont indicatifs et servent à la démonstration. Remplacez-les par une base de référence officielle avant toute mise en production.
    </x-nt.alert>

    <div class="space-y-6">
        @foreach ($factors as $category => $items)
            <x-nt.data-table :title="$category" :caption="'Facteurs — '.$category">
                <thead><tr><th scope="col">Facteur</th><th scope="col" class="text-right">Valeur</th><th scope="col">Unité</th><th scope="col">Source</th><th scope="col">Mis à jour</th></tr></thead>
                <tbody>
                    @foreach ($items as $factor)
                        <tr>
                            <td class="font-medium">{{ $factor->name }}</td>
                            <td class="text-right font-mono">{{ number_format($factor->value, $factor->value < 1 ? 3 : 2, ',', ' ') }}</td>
                            <td class="whitespace-nowrap text-muted-foreground">{{ $factor->unit }}</td>
                            <td class="text-xs text-muted-foreground">{{ $factor->source }}</td>
                            <td class="whitespace-nowrap text-xs text-muted-foreground">{{ $factor->updated_at->translatedFormat('M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-nt.data-table>
        @endforeach
    </div>
@endsection
