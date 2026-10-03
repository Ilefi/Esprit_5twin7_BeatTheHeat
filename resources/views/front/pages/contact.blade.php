@extends('layouts.front')

@section('title', 'Contact')

@section('hero')
    <x-nt.page-hero eyebrow="Contact" title="Parlons de traçabilité" subtitle="Une question, un partenariat, un projet de producteur ? Écrivez-nous."
                    :breadcrumb="[['label' => 'Contact']]" />
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container grid gap-10 lg:grid-cols-[1.5fr_1fr]">
            <x-nt.card>
                <form method="POST" action="{{ route('front.contact.send') }}" class="grid gap-5 sm:grid-cols-2" novalidate>
                    @csrf
                    <x-nt.form.input name="name" label="Nom complet" required autocomplete="name" :value="auth()->user()?->name" />
                    <x-nt.form.input name="email" type="email" label="Adresse e-mail" required autocomplete="email" :value="auth()->user()?->email" />
                    <x-nt.form.select name="subject" label="Sujet" :options="$subjects" placeholder="Choisissez un sujet" required class="sm:col-span-2" />
                    <x-nt.form.textarea name="message" label="Votre message" required :maxlength="2000" rows="6" hint="20 caractères minimum." class="sm:col-span-2" />
                    <div class="sm:col-span-2">
                        <x-nt.button type="submit" icon="fa-paper-plane">Envoyer le message</x-nt.button>
                    </div>
                </form>
            </x-nt.card>

            <div class="space-y-4">
                @foreach ([
                    ['fa-location-dot', 'Adresse', 'Esprit — Pôle technologique El Ghazala, Ariana, Tunisie'],
                    ['fa-envelope', 'E-mail', 'contact@nutritrace.tn'],
                    ['fa-clock', 'Délai de réponse', 'Sous 48 heures ouvrées'],
                ] as [$icon, $title, $text])
                    <div class="nt-card flex items-start gap-4 p-5">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-primary/12 text-primary-strong"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="text-sm font-semibold">{{ $title }}</h2>
                            <p class="text-sm text-muted-foreground">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
                <x-nt.alert type="info" title="Un produit suspect ?">
                    Utilisez plutôt le <a href="{{ route('front.reports.create') }}" class="nt-link">formulaire de signalement</a> : il est suivi et traité en priorité.
                </x-nt.alert>
            </div>
        </div>
    </section>
@endsection
