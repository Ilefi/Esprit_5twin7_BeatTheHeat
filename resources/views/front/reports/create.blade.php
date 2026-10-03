@extends('layouts.front')

@section('title', 'Nouveau signalement')
@section('meta_description', 'Signalez une allégation douteuse, une certification suspecte ou une incohérence de traçabilité.')

@php
    $steps = [1 => ['Type', 'fa-tags'], 2 => ['Cible', 'fa-crosshairs'], 3 => ['Description', 'fa-align-left'], 4 => ['Récapitulatif', 'fa-clipboard-check']];
    $targetTypes = ['product' => ['Produit', 'fa-basket-shopping'], 'actor' => ['Acteur', 'fa-industry'], 'certification' => ['Certification', 'fa-award']];
@endphp

@section('hero')
    <x-nt.page-hero eyebrow="Module 4 · Signalements" title="Signaler une information douteuse"
                    subtitle="Votre signalement est examiné par un modérateur sous 7 jours. Vous suivez chaque étape depuis votre espace."
                    :breadcrumb="[['label' => 'Observatoire', 'url' => route('front.observatory')], ['label' => 'Nouveau signalement']]" />
@endsection

@section('content')
    <section class="nt-container max-w-4xl py-10">
        @if ($errors->any())
            <x-nt.alert type="danger" title="Le formulaire contient {{ $errors->count() }} erreur{{ $errors->count() > 1 ? 's' : '' }}" class="mb-6">
                Nous avons rouvert l'étape concernée. Corrigez les champs signalés puis renvoyez le formulaire.
            </x-nt.alert>
        @endif

        <form method="POST" action="{{ route('front.reports.store') }}" enctype="multipart/form-data" novalidate
              x-data="ntReportWizard(@js($prefill + ['types' => $types, 'targets' => $targets]))"
              class="nt-card overflow-hidden">
            @csrf

            {{-- Progress --}}
            <div class="border-b bg-muted/40 px-5 py-5 sm:px-8">
                <div class="mb-4 flex items-center justify-between text-sm">
                    <p class="font-semibold">Étape <span x-text="step">{{ $prefill['step'] }}</span> sur 4</p>
                    <p class="text-muted-foreground" x-text="progress + ' %'"></p>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-border" role="progressbar" aria-label="Progression du signalement" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progress">
                    <div class="h-full rounded-full bg-primary transition-all duration-300" :style="`width: ${progress}%`"></div>
                </div>
                <ol class="mt-4 grid grid-cols-4 gap-2 text-center text-xs">
                    @foreach ($steps as $number => [$label, $icon])
                        <li>
                            <button type="button" x-on:click="goTo({{ $number }})" :disabled="{{ $number }} >= step" :aria-current="step === {{ $number }} ? 'step' : null"
                                    class="flex w-full flex-col items-center gap-1.5 font-semibold disabled:cursor-default">
                                <span class="grid h-9 w-9 place-items-center rounded-full transition"
                                      :class="step > {{ $number }} ? 'bg-primary text-primary-foreground' : (step === {{ $number }} ? 'bg-primary/15 text-primary-strong ring-2 ring-primary' : 'bg-surface text-muted-foreground ring-1 ring-border')">
                                    <i class="fa-solid" :class="step > {{ $number }} ? 'fa-check' : '{{ $icon }}'" aria-hidden="true"></i>
                                </span>
                                <span class="hidden sm:block" :class="step >= {{ $number }} ? 'text-foreground' : 'text-muted-foreground'">{{ $label }}</span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="px-5 py-8 sm:px-8">
                {{-- Step 1 — Type --}}
                <fieldset data-step="1" x-show="step === 1" @if ($prefill['step'] !== 1) x-cloak @endif>
                    <legend class="contents"><h2 tabindex="-1" class="text-xl font-semibold focus:outline-none">Quel est le problème ?</h2></legend>
                    <p class="mt-1 text-sm text-muted-foreground">Choisissez la catégorie qui décrit le mieux votre signalement.</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($types as $type)
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="{{ $type['value'] }}" x-model="type" class="peer sr-only">
                                <span class="flex h-full items-start gap-4 rounded-xl border-2 p-4 transition hover:border-primary/40 peer-checked:border-primary peer-checked:bg-primary/6 peer-focus-visible:ring-2 peer-focus-visible:ring-ring">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-muted text-lg text-foreground"><i class="fa-solid {{ $type['icon'] }}" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="block font-semibold">{{ $type['label'] }}</span>
                                        <span class="block text-sm text-muted-foreground">{{ $type['hint'] }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('type')<p class="mt-3 text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>@enderror
                </fieldset>

                {{-- Step 2 — Target --}}
                <fieldset data-step="2" x-show="step === 2" @if ($prefill['step'] !== 2) x-cloak @endif>
                    <legend class="contents"><h2 tabindex="-1" class="text-xl font-semibold focus:outline-none">Que concerne votre signalement ?</h2></legend>
                    <input type="hidden" name="target_type" :value="targetType">
                    <div class="mt-5 inline-flex rounded-lg border bg-muted/50 p-1" role="group" aria-label="Type de cible">
                        @foreach ($targetTypes as $value => [$label, $icon])
                            <button type="button" x-on:click="setTargetType('{{ $value }}')" :aria-pressed="(targetType === '{{ $value }}').toString()"
                                    class="nt-btn nt-btn-sm" :class="targetType === '{{ $value }}' ? 'nt-btn-primary' : 'nt-btn-ghost'">
                                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <label for="target-search" class="nt-label">Rechercher</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground" aria-hidden="true"></i>
                            <input id="target-search" type="search" x-model="search" placeholder="Tapez un nom…" class="nt-input ps-10" autocomplete="off">
                        </div>
                    </div>

                    <div class="mt-4 max-h-72 space-y-2 overflow-y-auto pe-1" role="radiogroup" aria-label="Cible">
                        <template x-for="target in filteredTargets" :key="targetType + target.id">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition hover:bg-muted/50"
                                   :class="String(targetId) === String(target.id) && 'border-primary bg-primary/6'">
                                <input type="radio" name="target_id" :value="target.id" x-model="targetId" class="nt-checkbox">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium" x-text="target.name"></span>
                                    <span class="block truncate text-xs text-muted-foreground" x-text="target.subtitle"></span>
                                </span>
                            </label>
                        </template>
                        <p x-show="filteredTargets.length === 0" class="py-6 text-center text-sm text-muted-foreground">Aucun résultat.</p>
                    </div>
                    @error('target_id')<p class="mt-3 text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>@enderror
                </fieldset>

                {{-- Step 3 — Description & evidence --}}
                <fieldset data-step="3" x-show="step === 3" @if ($prefill['step'] !== 3) x-cloak @endif class="space-y-5">
                    <legend class="contents"><h2 tabindex="-1" class="text-xl font-semibold focus:outline-none">Décrivez ce que vous avez constaté</h2></legend>
                    <div>
                        <label for="description" class="nt-label">Description <span class="text-danger-strong" aria-hidden="true">*</span></label>
                        <textarea id="description" name="description" rows="6" maxlength="3000" x-model="description" class="nt-input"
                                  placeholder="Où avez-vous vu l'allégation ? En quoi est-elle trompeuse ?"
                                  @error('description') aria-invalid="true" aria-describedby="description-error" @else aria-describedby="description-hint" @enderror></textarea>
                        <div class="mt-1.5 flex justify-between gap-3 text-xs">
                            @error('description')
                                <p id="description-error" class="text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>
                            @else
                                <p id="description-hint" class="text-muted-foreground">30 caractères minimum.</p>
                            @enderror
                            <p class="tabular-nums text-muted-foreground"><span x-text="description.length"></span> / 3000</p>
                        </div>
                    </div>
                    <x-nt.form.file name="evidence[]" label="Pièces jointes (photos, documents)" multiple hint="Jusqu'à 5 fichiers PDF, JPG ou PNG — 5 Mo maximum chacun." />
                    <div>
                        <label for="links" class="nt-label">Liens utiles <span class="font-normal text-muted-foreground">(facultatif)</span></label>
                        <textarea id="links" name="links" rows="2" x-model="links" class="nt-input" placeholder="https://… (un lien par ligne)"></textarea>
                        @error('links')<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
                    </div>
                </fieldset>

                {{-- Step 4 — Summary --}}
                <fieldset data-step="4" x-show="step === 4" @if ($prefill['step'] !== 4) x-cloak @endif>
                    <legend class="contents"><h2 tabindex="-1" class="text-xl font-semibold focus:outline-none">Vérifiez avant d'envoyer</h2></legend>
                    <dl class="mt-5 divide-y rounded-xl border">
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between">
                            <dt class="text-sm text-muted-foreground">Type</dt>
                            <dd class="font-semibold" x-text="typeLabel"></dd>
                        </div>
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between">
                            <dt class="text-sm text-muted-foreground">Cible</dt>
                            <dd class="font-semibold" x-text="targetLabel"></dd>
                        </div>
                        <div class="p-4">
                            <dt class="text-sm text-muted-foreground">Description</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm" x-text="description || '—'"></dd>
                        </div>
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between">
                            <dt class="text-sm text-muted-foreground">Liens</dt>
                            <dd class="text-sm" x-text="links || 'Aucun'"></dd>
                        </div>
                    </dl>
                    <div class="mt-5 rounded-xl bg-muted/50 p-4">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="checkbox" name="consent" value="1" x-model="consent" class="nt-checkbox mt-0.5" @error('consent') aria-invalid="true" @enderror>
                            <span class="text-sm">J'atteste que les informations fournies sont exactes à ma connaissance et j'accepte qu'elles soient transmises, anonymisées, à l'acteur concerné.</span>
                        </label>
                        @error('consent')<p class="mt-2 text-sm text-danger-strong"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>{{ $message }}</p>@enderror
                    </div>
                </fieldset>
            </div>

            <div class="flex items-center justify-between gap-3 border-t bg-muted/40 px-5 py-4 sm:px-8">
                <x-nt.button variant="ghost" icon="fa-arrow-left" x-show="step > 1" x-on:click="prev()">Précédent</x-nt.button>
                <span x-show="step === 1"></span>
                <x-nt.button icon-right="fa-arrow-right" x-show="step < total" x-on:click="next()" ::disabled="! canContinue()">Continuer</x-nt.button>
                <x-nt.button type="submit" variant="danger" icon="fa-paper-plane" x-show="step === total" x-cloak ::disabled="! consent">Envoyer le signalement</x-nt.button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            <i class="fa-solid fa-lock me-1" aria-hidden="true"></i> Votre identité n'est jamais rendue publique. Les cas fondés sont publiés anonymement dans l'<a href="{{ route('front.observatory') }}" class="nt-link">Observatoire</a>.
        </p>
    </section>
@endsection
