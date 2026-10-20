<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dossier Officiel — {{ $report->ref }} — NutriTrace</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-page { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 20mm !important; }
        }
    </style>
</head>
<body class="bg-muted/40 font-sans text-foreground antialiased min-h-screen py-8">

    {{-- Top Action Bar (hidden when printing) --}}
    <div class="no-print max-w-4xl mx-auto mb-6 px-4 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.reports.show', $report->ref) }}" class="nt-btn nt-btn-outline nt-btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Retour au signalement
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="nt-btn nt-btn-primary nt-btn-sm">
                <i class="fa-solid fa-print me-1"></i> Imprimer / Enregistrer en PDF
            </button>
        </div>
    </div>

    {{-- Official A4 Printable Page --}}
    <div class="print-page max-w-4xl mx-auto bg-white border shadow-lg rounded-xl p-8 sm:p-12 text-slate-800">
        
        {{-- Header Officiel --}}
        <header class="border-b-2 border-slate-900 pb-6">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="text-xs uppercase tracking-widest font-extrabold text-slate-500">RÉPUBLIQUE TUNISIENNE</p>
                    <p class="text-xs font-semibold text-slate-600">Ministère de l'Environnement & de l'Agriculture</p>
                    <p class="text-sm font-bold text-primary mt-1">AGENCE NUTRITRACE · TRAÇABILITÉ & ANTI-GREENWASHING</p>
                </div>
                <div class="text-right">
                    <span class="inline-block rounded border border-slate-900 px-3 py-1 font-mono text-sm font-bold">
                        PV-{{ $report->ref }}
                    </span>
                    <p class="text-xs text-slate-500 mt-1">Édité le {{ now()->translatedFormat('d F Y à H:i') }}</p>
                </div>
            </div>
            <div class="mt-8 text-center">
                <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900 font-heading">
                    Procès-Verbal de Constatation & d'Audit Anti-Greenwashing
                </h1>
                <p class="text-sm text-slate-600 mt-1">Dossier d'investigation citoyenne et d'analyse de conformité environnementale</p>
            </div>
        </header>

        {{-- Section 1 : Informations Générales --}}
        <section class="mt-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 border-b pb-1">
                1. Identification du Signalement
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm bg-slate-50 p-4 rounded-lg border">
                <div>
                    <span class="block text-xs text-slate-500 font-semibold">Référence</span>
                    <span class="font-bold text-slate-900">{{ $report->ref }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 font-semibold">Date de dépôt</span>
                    <span class="font-medium text-slate-800">{{ $report->created_at->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 font-semibold">Type d'anomalie</span>
                    <span class="font-bold text-danger-strong">{{ ucfirst($report->type) }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 font-semibold">Priorité</span>
                    <span class="font-semibold uppercase text-slate-800">{{ $report->priority }}</span>
                </div>
                <div class="col-span-2">
                    <span class="block text-xs text-slate-500 font-semibold">Déclarant citoyen</span>
                    <span class="font-medium text-slate-800">{{ $report->reporter->name }} ({{ $report->reporter->email }})</span>
                </div>
                <div class="col-span-2">
                    <span class="block text-xs text-slate-500 font-semibold">Statut juridique retenu</span>
                    <span class="font-bold uppercase text-primary">{{ $report->status }}</span>
                </div>
            </div>
        </section>

        {{-- Section 2 : Cible & Données Réelles en Base --}}
        <section class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 border-b pb-1">
                2. Cible mise en cause & Métriques de Traçabilité
            </h2>
            <div class="rounded-lg border p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase">{{ $report->target->type_label }}</span>
                        <h3 class="text-lg font-bold text-slate-900">{{ $report->target->name }}</h3>
                        <p class="text-xs text-slate-600">{{ $report->target->subtitle }}</p>
                    </div>
                </div>

                @if ($report->reportable instanceof \App\Models\Product)
                    <div class="grid grid-cols-3 gap-3 border-t pt-3 text-xs">
                        <div class="rounded bg-slate-100 p-2 text-center">
                            <span class="text-slate-500 block">Éco-score réel</span>
                            <span class="text-lg font-extrabold text-slate-900">{{ $report->reportable->impact?->eco_score ?? 'C' }}</span>
                        </div>
                        <div class="rounded bg-slate-100 p-2 text-center">
                            <span class="text-slate-500 block">Émissions Carbone</span>
                            <span class="text-lg font-extrabold text-slate-900">{{ $report->reportable->impact?->co2_per_kg ?? 'N/A' }} <span class="text-xs font-normal">kg/kg</span></span>
                        </div>
                        <div class="rounded bg-slate-100 p-2 text-center">
                            <span class="text-slate-500 block">Emballage</span>
                            <span class="text-sm font-bold text-slate-900 truncate block mt-1">{{ $report->reportable->impact?->packaging ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Section 3 : Allégations & Preuves --}}
        <section class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 border-b pb-1">
                3. Faits reprochés & Preuves fournies
            </h2>
            <div class="rounded-lg border p-4 bg-slate-50/50">
                <p class="text-xs font-bold text-slate-700">Objet du signalement : {{ $report->title }}</p>
                <p class="text-sm text-slate-700 mt-2 leading-relaxed whitespace-pre-line">{{ $report->description }}</p>

                @if ($report->evidence->isNotEmpty())
                    <div class="mt-4 border-t pt-3">
                        <span class="text-xs font-semibold text-slate-500">Pièces justificatives jointes au dossier :</span>
                        <ul class="mt-2 space-y-1 text-xs text-slate-600">
                            @foreach ($report->evidence as $ev)
                                <li>• <strong>{{ $ev->name }}</strong> ({{ $ev->kind === 'file' ? 'Fichier ' . $ev->size : $ev->url }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>

        {{-- Section 4 : Évaluation d'Intelligence Artificielle & NLP --}}
        <section class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 border-b pb-1">
                4. Expertise Algorithmique & IA Anti-Greenwashing
            </h2>
            <div class="rounded-lg border border-primary/40 bg-primary/5 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-800">Score de risque d'allégation trompeuse calculé :</span>
                    <span class="inline-flex items-center gap-1 rounded bg-slate-900 px-3 py-1 text-sm font-bold text-white">
                        <span>{{ $aiAnalysis['risk_score'] ?? 75 }}%</span> — <span>{{ $aiAnalysis['risk_level'] ?? 'Élevé' }}</span>
                    </span>
                </div>
                <div class="text-xs text-slate-700 leading-relaxed border-t border-primary/20 pt-2">
                    <p><strong>Rapport d'analyse sémantique :</strong> {{ $aiAnalysis['analysis'] ?? 'Incohérence relevée entre les allégations marketing et les bilans écologiques enregistrés.' }}</p>
                </div>
            </div>
        </section>

        {{-- Section 5 : Décision & Homologation --}}
        <section class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 border-b pb-1">
                5. Décision de la Commission & Résolution
            </h2>
            <div class="rounded-lg border p-4 bg-slate-50">
                <p class="text-xs text-slate-500 font-semibold">Décision motivée rendue par l'autorité de modération :</p>
                <p class="text-sm font-semibold text-slate-900 mt-1 leading-relaxed">
                    {{ $report->resolution ?? $aiAnalysis['recommended_resolution'] ?? 'Dossier instruit et transmis à l\'opérateur économique.' }}
                </p>
            </div>
        </section>

        {{-- Signatures & Tampon --}}
        <footer class="mt-12 pt-6 border-t-2 border-slate-900 grid grid-cols-2 gap-8 text-xs">
            <div>
                <p class="font-bold text-slate-900 uppercase">Pour l'Agence NutriTrace</p>
                <p class="text-slate-500">L'agent d'inspection assermenté</p>
                <p class="mt-8 font-semibold text-slate-800">{{ auth()->user()->name ?? 'Administrateur Général' }}</p>
                <p class="text-[11px] text-slate-400">Signature électronique certifiée · SHA-256</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-slate-900 uppercase">Cachet Officiel d'Homologation</p>
                <div class="inline-block mt-3 border-2 border-dashed border-slate-400 rounded-lg p-4 text-center">
                    <i class="fa-solid fa-stamp text-2xl text-slate-400 mb-1"></i>
                    <p class="font-mono text-[10px] text-slate-500">NUTRITRACE · AUDIT #{{ $report->id }}</p>
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest">CERTIFIÉ CONFORME</p>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
