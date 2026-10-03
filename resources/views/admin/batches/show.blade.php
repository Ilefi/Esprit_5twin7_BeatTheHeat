@extends('layouts.admin')

@section('title', 'Lot '.$batch->code)
@section('page_title', 'Lot '.$batch->code)
@section('page_subtitle', $batch->product->name.' · '.$batch->quantity)

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Lots', 'url' => route('admin.batches.index')], ['label' => $batch->code]]])
@endsection

@section('page_actions')
    <x-nt.button :href="route('front.traceability.batch', $batch->code)" variant="ghost" size="sm" icon="fa-arrow-up-right-from-square">Page publique</x-nt.button>
    <x-nt.button :href="route('admin.batches.edit', $batch->id)" variant="outline" size="sm" icon="fa-pen">Modifier le lot</x-nt.button>
    <x-nt.button size="sm" icon="fa-plus" x-data x-on:click="$dispatch('open-modal', 'new-step')">Ajouter une étape</x-nt.button>
@endsection

@php
    $stageIcons = ['production' => 'fa-tractor', 'processing' => 'fa-industry', 'distribution' => 'fa-truck', 'consumer' => 'fa-utensils'];
@endphp

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <x-nt.card>
            <x-slot:header>
                <h2 class="text-base font-semibold"><i class="fa-solid fa-timeline me-2 text-primary" aria-hidden="true"></i>Chronologie des étapes</h2>
                <span class="text-xs text-muted-foreground">Réordonnez avec les flèches</span>
            </x-slot:header>

            <ol class="space-y-3">
                @foreach ($batch->steps as $step)
                    <li class="flex gap-3 rounded-lg border p-4 transition hover:border-primary/40 sm:gap-4">
                        <div class="flex flex-col items-center gap-1">
                            <span class="grid h-10 w-10 place-items-center rounded-full {{ $step->verified ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }}">
                                <i class="fa-solid {{ $stageIcons[$step->stage] }}" aria-hidden="true"></i>
                            </span>
                            <span class="font-heading text-xs font-semibold text-muted-foreground">#{{ $step->position }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold">{{ $step->title }}</h3>
                                <span class="nt-badge">{{ $stages[$step->stage] }}</span>
                                @if ($step->verified)
                                    <x-nt.badge variant="success" icon="fa-circle-check">Vérifié</x-nt.badge>
                                @else
                                    <x-nt.badge variant="warning" icon="fa-hourglass-half">À vérifier</x-nt.badge>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ $step->actor?->name ?? 'Consommateur' }} · {{ $step->location }} · {{ $step->date->translatedFormat('d M Y') }}
                                @if ($step->distance_km) · +{{ $step->distance_km }} km @endif
                            </p>
                            <p class="mt-1 text-sm">{{ $step->action }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col gap-1 sm:flex-row sm:items-start">
                            @foreach (['up' => ['fa-arrow-up', 'Monter'], 'down' => ['fa-arrow-down', 'Descendre']] as $direction => [$icon, $label])
                                <form method="POST" action="{{ route('admin.batches.steps.move', [$batch->id, $step->id]) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="{{ $direction }}">
                                    <button type="submit" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" aria-label="{{ $label }} l'étape « {{ $step->title }} »"
                                            @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>
                                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endforeach
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm" x-data x-on:click="$dispatch('open-modal', 'step-{{ $step->id }}')" aria-label="Modifier l'étape « {{ $step->title }} »"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                            <button type="button" class="nt-btn nt-btn-ghost nt-btn-icon nt-btn-sm text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'delete-step-{{ $step->id }}')" aria-label="Supprimer l'étape « {{ $step->title }} »"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                        </div>
                    </li>
                @endforeach
            </ol>

            <button type="button" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed p-4 text-sm font-semibold text-muted-foreground transition hover:border-primary hover:text-primary-strong"
                    x-data x-on:click="$dispatch('open-modal', 'new-step')">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une étape
            </button>
        </x-nt.card>

        <div class="space-y-6">
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Résumé</h2></x-slot:header>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-muted-foreground">Statut</dt><dd><x-status-badge type="batch" :value="$batch->status" /></dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Produit</dt><dd class="text-right font-medium"><a href="{{ route('admin.products.show', $batch->product->id) }}" class="nt-link">{{ $batch->product->name }}</a></dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Production</dt><dd class="font-medium">{{ $batch->production_date->translatedFormat('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Étapes</dt><dd class="font-medium">{{ $batch->steps->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Distance totale</dt><dd class="font-medium">{{ $batch->total_km }} km</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Acteurs</dt><dd class="font-medium">{{ $batch->actors_count }}</dd></div>
                </dl>
            </x-nt.card>
            <x-nt.card class="text-center">
                <x-slot:header><h2 class="text-base font-semibold">QR code</h2></x-slot:header>
                <div class="mx-auto h-36 w-36 text-foreground" x-data="ntQr(@js(route('front.traceability.batch', $batch->code)))"></div>
                <p class="mt-2 font-mono text-xs">{{ $batch->code }}</p>
            </x-nt.card>
        </div>
    </div>

    @include('admin.batches._step-form', ['batch' => $batch, 'step' => null])
    @foreach ($batch->steps as $step)
        @include('admin.batches._step-form', ['batch' => $batch, 'step' => $step])
        @include('partials.admin.delete-modal', ['name' => 'delete-step-'.$step->id, 'action' => route('admin.batches.steps.destroy', [$batch->id, $step->id]), 'label' => 'l\'étape « '.$step->title.' »'])
    @endforeach
@endsection
