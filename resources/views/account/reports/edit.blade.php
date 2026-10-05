@extends('layouts.account')

@section('title', 'Modifier le signalement '.$report->ref)
@section('account_breadcrumb', 'Modifier '.$report->ref)
@section('account_title', 'Modifier le signalement '.$report->ref)
@section('account_subtitle', 'Vous pouvez modifier votre signalement tant qu\'il n\'a pas encore été examiné par un modérateur.')

@section('account_actions')
    <x-nt.button :href="route('account.reports.show', $report->ref)" variant="ghost" size="sm" icon="fa-arrow-left">Retour au signalement</x-nt.button>
@endsection

@section('account_content')
    <div class="nt-card p-6 max-w-3xl">
        <form method="POST" action="{{ route('account.reports.update', $report->ref) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Type de signalement --}}
            <div>
                <label for="report-type" class="nt-label font-semibold">Type de problème <span class="text-danger">*</span></label>
                <select id="report-type" name="type" class="nt-input mt-1.5 w-full rounded-lg border bg-surface p-3" required>
                    @foreach ($types as $t)
                        <option value="{{ $t['value'] }}" @selected(old('type', $report->type) === $t['value'])>
                            {{ $t['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('type')<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
            </div>

            {{-- Type de cible & Cible --}}
            <div x-data="{
                targetType: '{{ old('target_type', $report->reportable_type) }}',
                targetId: '{{ old('target_id', $report->reportable_id) }}',
                targets: @js($targets)
            }" class="space-y-4">
                <div>
                    <label class="nt-label font-semibold">Catégorie de la cible <span class="text-danger">*</span></label>
                    <div class="mt-2 flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="target_type" value="product" x-model="targetType" class="text-primary">
                            <span>Produit</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="target_type" value="actor" x-model="targetType" class="text-primary">
                            <span>Acteur / Entreprise</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="target_type" value="certification" x-model="targetType" class="text-primary">
                            <span>Certification / Label</span>
                        </label>
                    </div>
                    @error('target_type')<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="target_id" class="nt-label font-semibold">Élément concerné <span class="text-danger">*</span></label>
                    <select id="target_id" name="target_id" class="nt-input mt-1.5 w-full rounded-lg border bg-surface p-3" required>
                        <template x-for="item in (targets[targetType] || [])" :key="item.id">
                            <option :value="item.id" :selected="item.id == targetId" x-text="item.name + (item.subtitle ? ' (' + item.subtitle + ')' : '')"></option>
                        </template>
                    </select>
                    @error('target_id')<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Description détaillée --}}
            <div>
                <x-nt.form.textarea name="description" label="Description détaillée des faits" :value="old('description', $report->description)" rows="5" minlength="30" maxlength="3000" required
                    hint="Expliquez précisément ce qui pose problème (min. 30 caractères)." />
            </div>

            {{-- Pièces jointes actuelles & ajout --}}
            <div>
                <label class="nt-label font-semibold">Pièces jointes actuelles</label>
                <ul class="mt-2 space-y-2">
                    @forelse ($report->evidence as $evidence)
                        <li class="flex items-center gap-2 text-sm text-muted-foreground bg-muted/40 p-2 rounded-lg">
                            <i class="fa-solid {{ $evidence->kind === 'file' ? 'fa-file' : 'fa-link' }}"></i>
                            <span class="truncate font-medium text-foreground">{{ $evidence->name }}</span>
                            @isset($evidence->size)<span class="text-xs">({{ $evidence->size }})</span>@endisset
                        </li>
                    @empty
                        <li class="text-xs text-muted-foreground">Aucune pièce jointe.</li>
                    @endforelse
                </ul>

                <div class="mt-4">
                    <label for="evidence" class="nt-label font-semibold">Ajouter de nouvelles pièces jointes (photos, PDF)</label>
                    <input type="file" id="evidence" name="evidence[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="nt-input mt-1.5 w-full rounded-lg border bg-surface p-2 text-sm">
                    <p class="mt-1 text-xs text-muted-foreground">Formats acceptés : PDF, JPG, PNG (max 5 Mo par fichier, 5 fichiers max).</p>
                    @error('evidence.*')<p class="mt-1 text-sm text-danger-strong">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Liens --}}
            <div>
                <x-nt.form.input name="links" label="Lien web complémentaire (optionnel)" :value="old('links')" placeholder="https://..." maxlength="1000" />
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-5">
                <x-nt.button :href="route('account.reports.show', $report->ref)" variant="ghost">Annuler</x-nt.button>
                <x-nt.button type="submit" variant="primary" icon="fa-check">Enregistrer les modifications</x-nt.button>
            </div>
        </form>
    </div>
@endsection
