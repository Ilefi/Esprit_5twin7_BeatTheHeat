@extends('layouts.admin')

@section('title', 'Vérification des certificats')
@section('page_title', 'File de vérification')
@section('page_subtitle', 'Contrôlez les justificatifs transmis par les acteurs avant d\'afficher le badge « Vérifié ».')

@section('breadcrumb')
    @include('partials.shared.breadcrumb', ['items' => [['label' => 'Tableau de bord', 'url' => route('admin.dashboard')], ['label' => 'Certifications', 'url' => route('admin.certifications.index')], ['label' => 'Vérifications']]])
@endsection

@section('content')
    <nav aria-label="Filtrer par statut" class="mb-6 flex flex-wrap gap-2">
        @foreach ($statuses as $value => $label)
            <a href="{{ route('admin.certifications.verifications', ['statut' => $value]) }}" @if ($status === $value) aria-current="true" @endif
               @class(['nt-btn nt-btn-sm', 'nt-btn-primary' => $status === $value, 'nt-btn-outline' => $status !== $value])>
                {{ $label }} <span class="opacity-80">({{ $counts[$value] ?? 0 }})</span>
            </a>
        @endforeach
    </nav>

    <div class="grid gap-6 xl:grid-cols-2">
        @forelse ($verifications as $verification)
            <x-nt.card>
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <x-nt.cert-badge :certification="$verification->certification" />
                        <h2 class="text-base font-semibold">{{ $verification->actor->name }}</h2>
                    </div>
                    <x-status-badge type="verification" :value="$verification->status" />
                </x-slot:header>

                <div class="grid gap-5 sm:grid-cols-[10rem_minmax(0,1fr)]">
                    {{-- Proof preview (placeholder document) --}}
                    <div class="relative aspect-[3/4] overflow-hidden rounded-lg border bg-surface p-3 shadow-sm" aria-label="Aperçu du justificatif {{ $verification->document }}" role="img">
                        <div class="flex items-center gap-1.5 border-b pb-2"><i class="fa-solid fa-award text-gold-strong" aria-hidden="true"></i><span class="nt-skeleton h-2 w-16"></span></div>
                        <div class="mt-3 space-y-1.5">
                            @foreach ([20, 16, 18, 12, 18, 14] as $w)
                                <span class="nt-skeleton block h-1.5" style="width: {{ $w * 5 }}%"></span>
                            @endforeach
                        </div>
                        <span class="absolute bottom-3 right-3 grid h-10 w-10 place-items-center rounded-full border-2 border-primary/40 text-primary-strong"><i class="fa-solid fa-stamp" aria-hidden="true"></i></span>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-muted-foreground">Justificatif</dt><dd class="flex items-center gap-2 font-medium"><i class="fa-solid fa-file-pdf text-danger-strong" aria-hidden="true"></i>{{ $verification->document }}</dd></div>
                        <div><dt class="text-muted-foreground">N° de certificat</dt><dd class="font-mono">{{ $verification->certificate_number }}</dd></div>
                        <div><dt class="text-muted-foreground">Organisme</dt><dd>{{ $verification->certification->issuer }}</dd></div>
                        <div class="flex gap-6">
                            <div><dt class="text-muted-foreground">Soumis</dt><dd>{{ $verification->submitted_at->diffForHumans() }}</dd></div>
                            <div><dt class="text-muted-foreground">Expire le</dt><dd>{{ $verification->expires_at->translatedFormat('d M Y') }}</dd></div>
                        </div>
                        @if ($verification->rejection_reason)
                            <div class="rounded-lg bg-danger/8 p-3 text-danger-strong"><dt class="font-semibold">Motif du refus</dt><dd>{{ $verification->rejection_reason }}</dd></div>
                        @endif
                    </dl>
                </div>

                @if ($verification->status === 'pending')
                    <x-slot:footer>
                        <x-nt.button variant="ghost" icon="fa-xmark" class="text-danger-strong" x-data x-on:click="$dispatch('open-modal', 'reject-{{ $verification->id }}')">Refuser</x-nt.button>
                        <form method="POST" action="{{ route('admin.certifications.verifications.approve', $verification->id) }}">
                            @csrf
                            <x-nt.button type="submit" icon="fa-check">Approuver</x-nt.button>
                        </form>
                    </x-slot:footer>
                @endif
            </x-nt.card>

            @if ($verification->status === 'pending')
                @php $bag = 'reject'.$verification->id; @endphp
                <x-nt.modal :name="'reject-'.$verification->id" title="Refuser le certificat" :show="$errors->{$bag}->isNotEmpty()">
                    <form method="POST" action="{{ route('admin.certifications.verifications.reject', $verification->id) }}" id="reject-form-{{ $verification->id }}" class="space-y-4">
                        @csrf
                        <p class="text-sm text-muted-foreground">Le motif sera transmis à <strong class="text-foreground">{{ $verification->actor->name }}</strong>.</p>
                        <x-nt.form.textarea name="reason" label="Motif du refus" :bag="$bag" :id="'reason-'.$verification->id" rows="3" :maxlength="500" required hint="10 caractères minimum." />
                    </form>
                    <x-slot:footer>
                        <x-nt.button variant="ghost" x-on:click="$dispatch('close-modal')">Annuler</x-nt.button>
                        <x-nt.button type="submit" form="reject-form-{{ $verification->id }}" variant="danger" icon="fa-xmark">Refuser</x-nt.button>
                    </x-slot:footer>
                </x-nt.modal>
            @endif
        @empty
            <x-nt.empty-state icon="fa-file-circle-check" title="File vide" description="Aucun certificat dans cet état." class="xl:col-span-2" />
        @endforelse
    </div>
@endsection
