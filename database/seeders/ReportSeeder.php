<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Certification;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\View\Components\StatusBadge;
use Database\Factories\ReportFactory;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $targets = [
            'product' => Product::all()->keyBy('slug'),
            'actor' => Actor::all()->keyBy('slug'),
            'certification' => Certification::all()->keyBy('slug'),
        ];
        $users = User::pluck('id', 'email');

        // type, target type, target slug, title, status, priority, reporter, assignee, days ago, resolution
        $reports = [
            ['greenwashing', 'product', 'fromage-chevre-zaghouan', 'Emballage présenté comme « éco-responsable » alors qu\'il est en plastique', 'confirmed', 'high', 'salma.j@example.tn', 'admin@nutritrace.tn', 34, 'Allégation retirée de l\'étiquette et de la fiche produit sous 15 jours.'],
            ['misleading_footprint', 'product', 'tomates-sechees', 'Empreinte carbone affichée sous-estimée (transport non compté)', 'in_review', 'high', 'oussama.b@example.tn', 'rim.f@example.tn', 12, null],
            ['dubious_certification', 'actor', 'souk-ennour', 'Mention « bio » sur les supports sans certificat valide', 'pending', 'critical', 'mehdi.g@example.tn', null, 2, null],
            ['traceability_error', 'product', 'harissa-cap-bon', 'Étape d\'expédition du lot NT-2026-HAR-0058 non vérifiée', 'in_review', 'medium', 'consumer@nutritrace.tn', 'admin@nutritrace.tn', 5, null],
            ['health_quality', 'product', 'miel-thym-zaghouan', 'Pot de miel reçu avec un opercule endommagé', 'resolved', 'low', 'walid.c@example.tn', 'rim.f@example.tn', 40, 'Pot remplacé par le producteur, procédure de conditionnement revue.'],
            ['greenwashing', 'product', 'amandes-sfax', 'Slogan « zéro impact » sur des amandes très consommatrices d\'eau', 'confirmed', 'medium', 'mehdi.g@example.tn', 'rim.f@example.tn', 60, 'Slogan supprimé, empreinte eau désormais affichée sur l\'emballage.'],
            ['dubious_certification', 'certification', 'agriculture-raisonnee', 'Le label « raisonnée » est présenté comme équivalent au bio', 'confirmed', 'medium', 'ines.m@example.tn', 'admin@nutritrace.tn', 75, 'Ajout d\'une mention explicative sur la page du label et des produits concernés.'],
            ['other', 'product', 'sel-marin-sfax', 'Question sur l\'origine exacte des salines', 'rejected', 'low', 'oussama.b@example.tn', 'rim.f@example.tn', 28, 'Information déjà disponible : l\'origine est vérifiée sur le lot.'],
            ['traceability_error', 'actor', 'conserverie-cap-bon', 'Adresse de l\'atelier différente selon les lots', 'pending', 'medium', 'consumer@nutritrace.tn', null, 1, null],
            ['greenwashing', 'product', 'dattes-deglet-nour-tozeur', 'Visuel « récolte artisanale » sur un lot industriel ?', 'rejected', 'low', 'walid.c@example.tn', 'admin@nutritrace.tn', 50, 'Vérification sur site : récolte manuelle confirmée par la coopérative.'],
            ['misleading_footprint', 'product', 'couscous-complet', 'Score affiché A en magasin alors que la fiche indique C', 'resolved', 'high', 'salma.j@example.tn', 'rim.f@example.tn', 22, 'Étiquette de rayon corrigée chez le distributeur.'],
            ['health_quality', 'product', 'fromage-chevre-zaghouan', 'Date limite de consommation illisible', 'pending', 'high', 'ines.m@example.tn', null, 0, null],
            ['greenwashing', 'actor', 'marche-bio-tunis', 'Affiche « 100 % local » alors qu\'une partie des produits est importée', 'in_review', 'critical', 'oussama.b@example.tn', 'admin@nutritrace.tn', 8, null],
            ['dubious_certification', 'product', 'huile-olive-sfax', 'Numéro de certificat bio introuvable', 'resolved', 'medium', 'mehdi.g@example.tn', 'rim.f@example.tn', 90, 'Certificat valide retrouvé et lié au produit.'],
            ['other', 'actor', 'epicerie-verte', 'Suggestion : afficher les producteurs partenaires', 'rejected', 'low', 'consumer@nutritrace.tn', 'admin@nutritrace.tn', 15, 'Hors périmètre des signalements, transmis comme suggestion.'],
            ['traceability_error', 'product', 'oranges-maltaises-nabeul', 'Date de cueillette postérieure à la date de livraison', 'confirmed', 'high', 'consumer@nutritrace.tn', 'rim.f@example.tn', 45, 'Erreur de saisie corrigée par le producteur, lot revérifié.'],
        ];

        foreach ($reports as $i => [$type, $targetType, $target, $title, $status, $priority, $reporter, $assignee, $daysAgo, $resolution]) {
            $createdAt = now()->subDays($daysAgo)->subHours(3 + $i);

            $report = Report::factory()->about($targets[$targetType][$target])->create([
                'ref' => sprintf('SIG-2026-%04d', $i + 1),
                'type' => $type,
                'title' => $title,
                'description' => ReportFactory::DESCRIPTIONS[$type],
                'status' => $status,
                'priority' => $priority,
                'reporter_id' => $users[$reporter],
                'assignee_id' => $assignee ? $users[$assignee] : null,
                'resolution' => $resolution,
                'created_at' => $createdAt,
            ]);

            $this->recordWorkflow($report, withLink: $i % 3 !== 0);
        }

        // Older reports from the generated consumers, spread over the last 12 months (feeds the dashboard charts).
        $consumers = User::where('role', 'consumer')->where('email', 'not like', '%.tn')->get();
        $moderators = User::where('role', 'admin')->get();
        $products = $targets['product']->where('status', 'published')->values();

        Report::factory(24)
            ->recycle($consumers)
            ->state(fn () => [
                'reportable_id' => $products->random()->id,
                'assignee_id' => fake()->boolean(80) ? $moderators->random()->id : null,
            ])
            ->create()
            ->each(fn (Report $report) => $this->recordWorkflow($report, withLink: fake()->boolean()));
    }

    /**
     * Evidence, conversation, internal note and history matching the report's status.
     */
    private function recordWorkflow(Report $report, bool $withLink): void
    {
        $at = $report->created_at;
        $reporter = $report->reporter;
        $assignee = $report->assignee;
        $closed = in_array($report->status, Report::CLOSED_STATUSES, true);
        $decidedAt = $at->copy()->addDays(min(6, max(1, (int) $at->diffInDays(now()) - 1)));

        $report->evidence()->create(['kind' => 'file', 'name' => 'photo-etiquette.jpg', 'size' => '1,2 Mo']);
        if ($withLink) {
            $report->evidence()->create(['kind' => 'link', 'name' => 'Page produit du distributeur', 'url' => 'https://exemple.tn/produit']);
        }

        $report->history()->create(['label' => 'Signalement soumis', 'author' => $reporter->name, 'created_at' => $at]);
        $report->messages()->create(['user_id' => $reporter->id, 'role' => 'reporter', 'body' => 'Bonjour, je vous transmets ce signalement avec les éléments dont je dispose.', 'attachments' => ['photo-etiquette.jpg'], 'created_at' => $at]);

        if ($assignee) {
            $report->history()->create(['label' => 'Pris en charge par '.$assignee->name, 'author' => $assignee->name, 'created_at' => $at->copy()->addHours(6)]);
            $report->history()->create(['label' => 'Passé en examen', 'author' => $assignee->name, 'created_at' => $at->copy()->addHours(7)]);
            $report->notes()->create(['user_id' => $assignee->id, 'body' => 'Demande de justificatifs envoyée à l\'acteur (délai 7 jours).', 'created_at' => $at->copy()->addHours(7)]);
            $report->messages()->create(['user_id' => $assignee->id, 'role' => 'moderator', 'body' => 'Merci pour votre signalement. Nous avons contacté l\'acteur concerné et attendons ses justificatifs.', 'attachments' => [], 'created_at' => $at->copy()->addHours(8)]);
            $report->messages()->create(['user_id' => $reporter->id, 'role' => 'reporter', 'body' => 'Merci, j\'ajoute une seconde photo prise en magasin.', 'attachments' => ['photo-rayon.jpg'], 'created_at' => $at->copy()->addHours(20)]);
        }

        if ($closed) {
            $report->history()->create(['label' => 'Décision : '.StatusBadge::labelFor('report', $report->status), 'author' => $assignee?->name ?? 'Modération', 'created_at' => $decidedAt]);
        }

        $report->forceFill(['updated_at' => $closed ? $decidedAt : $at->copy()->addHours(8)])->saveQuietly();
    }
}
