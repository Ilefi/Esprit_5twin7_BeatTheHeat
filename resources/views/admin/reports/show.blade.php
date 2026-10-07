@extends('layouts.admin')

@section('title', 'Signalement '.$report->ref)
@section('page_title', $report->title)
@section('page_subtitle', $report->ref.' · reçu le '.$report->created_at->translatedFormat('d F Y à H:i'))

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Signalements', 'url' => route('admin.reports.index')], ['label' => $report->ref]]])
@endsection

@section('page_actions')
    <x-status-badge type="report" :value="$report->status" class="px-3 py-1 text-sm" />
    <x-status-badge type="priority" :value="$report->priority" class="px-3 py-1 text-sm" />
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            {{-- Report details & evidence --}}
            <x-nt.card>
                <x-slot:header>
                    <h2 class="text-base font-semibold">Détails du signalement</h2>
                    <x-status-badge type="report_type" :value="$report->type" />
                </x-slot:header>
                <div class="flex items-center gap-3">
                    <x-nt.avatar :name="$report->reporter->name" />
                    <div>
                        <p class="font-semibold">{{ $report->reporter->name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $report->reporter->email }} · {{ $report->reporter->reports_count }} signalement(s)</p>
                    </div>
                    <span class="ms-auto">@include('admin.reports._open-since', ['report' => $report])</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed">{{ $report->description }}</p>

                <h3 class="mt-6 text-sm font-semibold">Preuves jointes</h3>
                <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($report->evidence as $evidence)
                        <li class="flex items-center gap-3 rounded-lg border p-3">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg {{ $evidence->kind === 'file' ? 'bg-info/12 text-info-strong' : 'bg-muted text-muted-foreground' }}">
                                <i class="fa-solid {{ $evidence->kind === 'file' ? 'fa-image' : 'fa-link' }}" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $evidence->name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ $evidence->kind === 'file' ? 'Fichier · '.$evidence->size : $evidence->url }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-nt.card>

            {{-- Conversation + public reply --}}
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Échanges avec le déclarant</h2></x-slot:header>
                <ol class="space-y-4">
                    @foreach ($report->messages as $message)
                        <li @class(['flex gap-3', 'flex-row-reverse text-right' => $message->role === 'moderator'])>
                            <x-nt.avatar :name="$message->user->name" size="sm" />
                            <div @class(['max-w-[85%] rounded-xl p-3 text-left text-sm', 'bg-primary/10' => $message->role === 'moderator', 'bg-muted' => $message->role !== 'moderator'])>
                                <p class="text-xs font-semibold">{{ $message->user->name }} <span class="font-normal text-muted-foreground">· {{ $message->created_at->diffForHumans() }}</span></p>
                                <p class="mt-1">{{ $message->body }}</p>
                                @foreach ($message->attachments as $attachment)
                                    <span class="nt-badge mt-2 bg-surface"><i class="fa-solid fa-paperclip text-[0.7em]" aria-hidden="true"></i>{{ $attachment }}</span>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ol>
                <x-slot:footer class="flex-col items-stretch">
                    <form method="POST" action="{{ route('admin.reports.reply', $report->ref) }}" class="space-y-3">
                        @csrf
                        <x-nt.form.textarea name="reply" label="Réponse publique au déclarant" bag="reply" rows="3" :maxlength="2000" hint="Le déclarant est notifié par e-mail." />
                        <div class="flex justify-end"><x-nt.button type="submit" size="sm" icon="fa-paper-plane">Envoyer la réponse</x-nt.button></div>
                    </form>
                </x-slot:footer>
            </x-nt.card>
        </div>

        <div class="space-y-6">
            {{-- Target preview --}}
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Cible</h2></x-slot:header>
                <a href="{{ $report->target->url }}" class="flex items-center gap-3 rounded-lg border p-3 transition hover:border-primary/40 hover:bg-muted/40">
                    <span class="grid h-12 w-12 place-items-center rounded-lg bg-primary/12 text-lg text-primary-strong"><i class="fa-solid {{ $report->target->icon }}" aria-hidden="true"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs text-muted-foreground">{{ $report->target->type_label }}</span>
                        <span class="block truncate font-semibold">{{ $report->target->name }}</span>
                        <span class="block truncate text-xs text-muted-foreground">{{ $report->target->subtitle }}</span>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-muted-foreground" aria-hidden="true"></i>
                </a>
            </x-nt.card>

            {{-- Assistant IA Anti-Greenwashing --}}
            <div x-data="{
                loading: false,
                analyzed: false,
                data: null,
                analyze() {
                    this.loading = true;
                    fetch('{{ route('admin.reports.ai.analyze', $report->ref) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.data = res;
                        this.analyzed = true;
                        this.loading = false;
                    })
                    .catch(err => {
                        alert('Erreur lors de l\'analyse IA.');
                        this.loading = false;
                    });
                },
                applyRecommendation() {
                    if (!this.data) return;
                    const resField = document.querySelector('textarea[name=\'resolution\']');
                    if (resField) {
                        resField.value = this.data.recommended_resolution;
                    }
                    const statusField = document.querySelector('select[name=\'status\']');
                    if (statusField && this.data.recommended_status) {
                        statusField.value = this.data.recommended_status;
                    }
                }
            }" class="nt-card border-primary/30 bg-primary/5 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary text-primary-foreground">
                            <i class="fa-solid fa-robot"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-foreground">Assistant IA Anti-Greenwashing</h2>
                            <p class="text-xs text-muted-foreground">Analyse prédictive & aide à la décision</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="analyze()" :disabled="loading" class="nt-btn nt-btn-primary nt-btn-sm">
                        <template x-if="!loading">
                            <span><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Analyser avec l'IA</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fa-solid fa-spinner fa-spin me-1"></i> Analyse en cours...</span>
                        </template>
                    </button>
                </div>

                <div x-show="analyzed" x-transition class="mt-4 space-y-3 border-t border-primary/20 pt-4 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-muted-foreground">Indice de Greenwashing estimé :</span>
                        <span class="nt-badge" :class="data?.risk_score >= 70 ? 'bg-danger text-danger-foreground' : (data?.risk_score >= 45 ? 'bg-gold text-gold-foreground' : 'bg-primary text-primary-foreground')">
                            <strong x-text="data?.risk_score + '%'"></strong> · <span x-text="data?.risk_level"></span>
                        </span>
                    </div>
                    <div class="rounded-lg bg-surface p-3 border">
                        <p class="font-semibold text-foreground">💡 Constat de l'IA :</p>
                        <p class="mt-1 text-muted-foreground" x-text="data?.analysis"></p>
                    </div>
                    <div class="rounded-lg bg-surface p-3 border">
                        <p class="font-semibold text-foreground">⚖️ Décision suggérée :</p>
                        <p class="mt-1 text-muted-foreground" x-text="data?.recommended_resolution"></p>
                    </div>
                    <button type="button" x-on:click="applyRecommendation()" class="nt-btn nt-btn-outline nt-btn-sm w-full">
                        <i class="fa-solid fa-check me-1"></i> 🪄 Insérer automatiquement cette décision
                    </button>
                </div>
            </div>

            {{-- Workflow --}}
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Traitement</h2></x-slot:header>
                <form method="POST" action="{{ route('admin.reports.update', $report->ref) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-nt.form.select name="status" label="Statut" :options="$statuses" :value="$report->status" required />
                    <x-nt.form.select name="priority" label="Priorité" :options="$priorities" :value="$report->priority" required />
                    <x-nt.form.select name="assignee_id" label="Responsable" :options="$moderators" :value="$report->assignee?->id" placeholder="Non assigné" />
                    <x-nt.form.textarea name="resolution" label="Décision motivée" :value="$report->resolution" rows="3" :maxlength="1000" hint="Obligatoire pour clôturer (fondé, rejeté, résolu)." />
                    <x-nt.button type="submit" class="w-full" icon="fa-floppy-disk">Enregistrer</x-nt.button>
                </form>
            </x-nt.card>

            {{-- Internal notes --}}
            <x-nt.card>
                <x-slot:header>
                    <h2 class="text-base font-semibold">Notes internes</h2>
                    <x-nt.badge variant="earth" icon="fa-lock">Équipe</x-nt.badge>
                </x-slot:header>
                <ul class="space-y-3">
                    @forelse ($report->notes as $note)
                        <li class="rounded-lg bg-gold/12 p-3 text-sm">
                            <p>{{ $note->body }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $note->user->name }} · {{ $note->created_at->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">Aucune note pour l'instant.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('admin.reports.notes.store', $report->ref) }}" class="mt-4 space-y-3">
                    @csrf
                    <x-nt.form.textarea name="note" label="Ajouter une note" bag="note" rows="2" :maxlength="1000" />
                    <x-nt.button type="submit" variant="outline" size="sm" icon="fa-plus">Ajouter</x-nt.button>
                </form>
            </x-nt.card>

            {{-- History --}}
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Historique</h2></x-slot:header>
                <x-nt.timeline>
                    @foreach ($report->history->reverse() as $entry)
                        <x-nt.timeline-item icon="fa-clock-rotate-left" :tone="$loop->first ? 'primary' : 'muted'" :title="$entry->label" :time="$entry->created_at->translatedFormat('d M Y, H:i').' · '.$entry->author" />
                    @endforeach
                </x-nt.timeline>
            </x-nt.card>
        </div>
    </div>
@endsection
