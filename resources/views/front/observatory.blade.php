@extends('layouts.front')

@section('title', 'Observatoire du greenwashing')
@section('meta_description', 'Les signalements de greenwashing examinés sur NutriTrace : décisions publiques et anonymisées.')

@section('hero')
    <x-nt.page-hero eyebrow="Observatoire" title="Signalements"
        subtitle="Les signalements examinés par nos modérateurs, leur décision et les corrections obtenues. Publiés de façon anonyme."
        :breadcrumb="[['label' => 'Observatoire']]">
        <x-nt.button :href="route('front.reports.create')" variant="danger" icon="fa-flag">Faire un
            signalement</x-nt.button>
    </x-nt.page-hero>
@endsection

@section('content')
    <section class="nt-container py-10">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($kpis as $kpi)
                <x-nt.stat-card :icon="$kpi['icon']" :value="$kpi['value']" :label="$kpi['label']" :tone="$kpi['tone']" />
            @endforeach
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
            <figure class="nt-card p-5">
                <figcaption class="mb-4 font-semibold">Signalements par type</figcaption>
                <div class="h-72">
                    <canvas x-data="ntChart(@js($chart))" role="img"
                        aria-label="Nombre de signalements par type : {{ collect($chart['labels'])->zip($chart['datasets'][0]['data'])->map(fn($p) => $p[0] . ' ' . $p[1])->implode(', ') }}"></canvas>
                </div>
            </figure>
            <div class="nt-card flex flex-col justify-center gap-4 bg-earth p-6 text-earth-muted">
                <i class="fa-solid fa-scale-balanced text-3xl text-gold" aria-hidden="true"></i>
                <h2 class="text-xl font-semibold text-earth-foreground">Comment sont prises les décisions ?</h2>
                <p class="text-sm">Chaque signalement est confronté aux données de traçabilité et d'empreinte. L'acteur
                    concerné dispose de 7 jours pour fournir ses justificatifs. Un modérateur statue ensuite : fondé, rejeté
                    ou résolu après correction.</p>
                <a href="{{ route('front.faq') }}" class="text-sm font-semibold text-gold underline underline-offset-4">En
                    savoir plus</a>
            </div>
        </div>
    </section>

    <section class="nt-container pb-14" aria-labelledby="cases-title">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <h2 id="cases-title" class="text-2xl font-bold">Cas examinés</h2>
            <form method="GET" action="{{ route('front.observatory') }}" class="flex flex-wrap items-end gap-3">
                <x-nt.form.select name="type" label="Type" :options="$types" placeholder="Tous les types"
                    :value="request('type')" class="min-w-48" />
                <x-nt.form.select name="issue" label="Issue" :options="['confirmed' => 'Fondé', 'resolved' => 'Résolu']"
                    placeholder="Toutes" :value="request('issue')" />
                <x-nt.button type="submit" variant="outline" icon="fa-filter">Filtrer</x-nt.button>
            </form>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($publicReports as $report)
                <x-nt.report-card :report="$report" anonymized />
            @empty
                <x-nt.empty-state icon="fa-binoculars" title="Aucun cas pour ces filtres" class="md:col-span-2 lg:col-span-3" />
            @endforelse
        </div>

        <div class="mt-10">{{ $publicReports->links() }}</div>
    </section>
@endsection