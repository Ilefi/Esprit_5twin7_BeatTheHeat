@extends('layouts.front')

@section('title', 'Questions fréquentes')

@section('hero')
    <x-nt.page-hero eyebrow="FAQ" title="Questions fréquentes" subtitle="Tout ce qu'il faut savoir sur la traçabilité, l'éco-score et les signalements."
                    :breadcrumb="[['label' => 'FAQ']]" />
@endsection

@section('content')
    <section class="nt-section">
        <div class="nt-container grid gap-10 lg:grid-cols-[1.6fr_1fr]">
            <x-nt.accordion open="faq-0">
                @foreach ($faqs as $faq)
                    <x-nt.accordion-item :id="'faq-'.$loop->index" :title="$faq->question">{{ $faq->answer }}</x-nt.accordion-item>
                @endforeach
            </x-nt.accordion>

            <aside>
                <x-nt.card>
                    <x-slot:header>
                        <h2 class="text-base font-semibold"><i class="fa-solid fa-life-ring me-2 text-primary" aria-hidden="true"></i>Pas de réponse ?</h2>
                    </x-slot:header>
                    <p class="text-sm text-muted-foreground">Notre équipe répond sous 48 heures ouvrées.</p>
                    <x-slot:footer>
                        <x-nt.button :href="route('front.contact')" size="sm" icon="fa-envelope">Nous écrire</x-nt.button>
                    </x-slot:footer>
                </x-nt.card>
            </aside>
        </div>
    </section>
@endsection
