@extends('layouts.account')

@section('title', 'Signalement '.$report->ref)
@section('account_breadcrumb', $report->ref)
@section('account_title', $report->title)
@section('account_subtitle', 'Référence '.$report->ref.' · envoyé le '.$report->created_at->translatedFormat('d F Y'))

@section('account_actions')
    <x-nt.button :href="route('account.reports.index')" variant="ghost" size="sm" icon="fa-arrow-left">Mes signalements</x-nt.button>
@endsection

@php
    $stages = [1 => ['Soumis', 'fa-paper-plane'], 2 => ['En examen', 'fa-magnifying-glass'], 3 => ['Décision', 'fa-gavel'], 4 => ['Clôturé', 'fa-flag-checkered']];
@endphp

@section('account_content')
    {{-- Status stepper --}}
    <div class="nt-card p-5 sm:p-6">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <x-status-badge type="report" :value="$report->status" />
            <x-status-badge type="report_type" :value="$report->type" />
        </div>
        <ol class="grid grid-cols-4" aria-label="Avancement du signalement">
            @foreach ($stages as $number => [$label, $icon])
                <li class="relative flex flex-col items-center text-center" @if ($number === $stage) aria-current="step" @endif>
                    @unless ($loop->last)
                        <span @class(['absolute left-1/2 top-5 h-1 w-full rounded-full', 'bg-primary' => $number < $stage, 'bg-border' => $number >= $stage]) aria-hidden="true"></span>
                    @endunless
                    <span @class([
                        'relative z-10 grid h-10 w-10 place-items-center rounded-full text-sm ring-4 ring-surface',
                        'bg-primary text-primary-foreground' => $number <= $stage,
                        'bg-muted text-muted-foreground' => $number > $stage,
                        'nt-pulse-ring' => $number === $stage && $stage < 4,
                    ])>
                        <i class="fa-solid {{ $number < $stage ? 'fa-check' : $icon }}" aria-hidden="true"></i>
                    </span>
                    <span @class(['mt-2 text-xs font-semibold sm:text-sm', 'text-foreground' => $number <= $stage, 'text-muted-foreground' => $number > $stage])>{{ $label }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            @if ($report->resolution)
                <div @class(['rounded-xl border-2 p-5', 'border-danger/40 bg-danger/6' => $report->status === 'confirmed', 'border-primary/40 bg-primary/6' => $report->status === 'resolved', 'border-border bg-muted/50' => $report->status === 'rejected'])>
                    <p class="flex items-center gap-2 font-heading font-semibold"><i class="fa-solid fa-gavel" aria-hidden="true"></i> Décision : {{ \App\View\Components\StatusBadge::labelFor('report', $report->status) }}</p>
                    <p class="mt-2 text-sm text-foreground/85">{{ $report->resolution }}</p>
                </div>
            @endif

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Échanges avec la modération</h2></x-slot:header>
                <ol class="space-y-4">
                    @foreach ($report->messages as $message)
                        <li @class(['flex gap-3', 'flex-row-reverse text-right' => $message->role === 'reporter'])>
                            <x-nt.avatar :name="$message->author" size="sm" />
                            <div @class(['max-w-[85%] rounded-xl p-3 text-sm', 'bg-primary/10' => $message->role === 'reporter', 'bg-muted' => $message->role !== 'reporter'])>
                                <p class="text-xs font-semibold">
                                    {{ $message->role === 'reporter' ? 'Vous' : $message->author.' · Modération' }}
                                    <span class="font-normal text-muted-foreground">· {{ $message->at->diffForHumans() }}</span>
                                </p>
                                <p class="mt-1 text-left">{{ $message->body }}</p>
                                @foreach ($message->attachments as $attachment)
                                    <span class="nt-badge mt-2 bg-surface"><i class="fa-solid fa-paperclip text-[0.7em]" aria-hidden="true"></i>{{ $attachment }}</span>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ol>

                <x-slot:footer class="flex-col items-stretch">
                    @if (in_array($report->status, ['pending', 'in_review'], true))
                        <form method="POST" action="{{ route('account.reports.message', $report->ref) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <x-nt.form.textarea name="message" label="Votre message" rows="3" :maxlength="2000" required />
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <label class="nt-btn nt-btn-ghost nt-btn-sm cursor-pointer">
                                    <i class="fa-solid fa-paperclip" aria-hidden="true"></i> Joindre un fichier
                                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                </label>
                                <x-nt.button type="submit" size="sm" icon="fa-paper-plane">Envoyer</x-nt.button>
                            </div>
                            @error('attachment')<p class="text-sm text-danger-strong">{{ $message }}</p>@enderror
                        </form>
                    @else
                        <p class="text-sm text-muted-foreground"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Ce dossier est clôturé : les échanges sont fermés.</p>
                    @endif
                </x-slot:footer>
            </x-nt.card>
        </div>

        <div class="space-y-6">
            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Cible du signalement</h2></x-slot:header>
                <a href="{{ $report->target->url }}" class="flex items-center gap-3 rounded-lg border p-3 transition hover:border-primary/40">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-muted"><i class="fa-solid {{ $report->target->icon }}" aria-hidden="true"></i></span>
                    <span class="min-w-0">
                        <span class="block text-xs text-muted-foreground">{{ $report->target->type_label }}</span>
                        <span class="block truncate font-semibold">{{ $report->target->name }}</span>
                    </span>
                </a>
                <h3 class="mt-5 text-sm font-semibold">Votre description</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ $report->description }}</p>
                <h3 class="mt-5 text-sm font-semibold">Pièces jointes</h3>
                <ul class="mt-2 space-y-2">
                    @foreach ($report->evidence as $evidence)
                        <li class="flex items-center gap-2 text-sm">
                            <i class="fa-solid {{ $evidence->kind === 'file' ? 'fa-file-image' : 'fa-link' }} text-muted-foreground" aria-hidden="true"></i>
                            <span class="truncate">{{ $evidence->name }}</span>
                            @isset($evidence->size)<span class="text-xs text-muted-foreground">{{ $evidence->size }}</span>@endisset
                        </li>
                    @endforeach
                </ul>
            </x-nt.card>

            <x-nt.card>
                <x-slot:header><h2 class="text-base font-semibold">Historique</h2></x-slot:header>
                <x-nt.timeline>
                    @foreach ($report->history->reverse() as $entry)
                        <x-nt.timeline-item icon="fa-clock-rotate-left" :tone="$loop->first ? 'primary' : 'muted'" :title="$entry->label" :time="$entry->at->translatedFormat('d M Y, H:i')" />
                    @endforeach
                </x-nt.timeline>
            </x-nt.card>
        </div>
    </div>
@endsection
