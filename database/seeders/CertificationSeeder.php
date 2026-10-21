<?php

namespace Database\Seeders;

use App\Models\Certification;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $certifications = [
            [
                'slug' => 'bio', 'name' => 'Agriculture biologique', 'short_name' => 'Bio', 'type' => 'bio',
                'issuer' => 'Organisme de contrôle bio agréé (exemple)',
                'expires_at' => now()->addMonths(14)->toDateString(),
                'description' => 'Garantit une production sans pesticides ni engrais chimiques de synthèse, sans OGM, avec un contrôle annuel sur site.',
                'criteria' => ['Aucun intrant chimique de synthèse', 'Période de conversion de 2 à 3 ans', 'Rotation des cultures', 'Contrôle annuel + contrôles inopinés'],
                'guarantees' => ['Mode de production contrôlé', 'Absence d\'OGM', 'Traçabilité documentaire'],
                'limits' => ['Ne garantit pas une origine locale', 'Ne mesure pas l\'empreinte carbone du transport'],
            ],
            [
                'slug' => 'local', 'name' => 'Produit local', 'short_name' => 'Local', 'type' => 'local',
                'issuer' => 'Charte régionale des circuits courts (exemple)',
                'expires_at' => null,
                'description' => 'Produit cultivé et transformé à moins de 250 km du point de vente, avec au plus un intermédiaire.',
                'criteria' => ['Moins de 250 km entre champ et rayon', 'Un intermédiaire maximum', 'Origine déclarée et vérifiable'],
                'guarantees' => ['Proximité géographique', 'Transport réduit'],
                'limits' => ['Ne garantit pas un mode de culture biologique'],
            ],
            [
                'slug' => 'equitable', 'name' => 'Commerce équitable', 'short_name' => 'Équitable', 'type' => 'fair',
                'issuer' => 'Réseau du commerce équitable (exemple)',
                'expires_at' => now()->addMonths(20)->toDateString(),
                'description' => 'Assure un prix minimum garanti aux producteurs et une prime de développement pour la coopérative.',
                'criteria' => ['Prix minimum garanti', 'Contrats pluriannuels', 'Prime de développement', 'Gouvernance démocratique'],
                'guarantees' => ['Rémunération juste du producteur', 'Relation commerciale durable'],
                'limits' => ['Ne certifie pas à lui seul le mode de culture'],
            ],
            [
                'slug' => 'origine-protegee', 'name' => 'Origine protégée', 'short_name' => 'Origine', 'type' => 'origin',
                'issuer' => 'Comité des appellations d\'origine (exemple)',
                'expires_at' => null,
                'description' => 'Lie le produit à un terroir précis et à un savoir-faire traditionnel décrit dans un cahier des charges.',
                'criteria' => ['Zone géographique délimitée', 'Cahier des charges de production', 'Contrôle par un organisme tiers'],
                'guarantees' => ['Origine géographique', 'Savoir-faire traditionnel'],
                'limits' => ['Ne garantit pas l\'absence de pesticides'],
            ],
            [
                'slug' => 'sans-pesticides', 'name' => 'Sans résidus de pesticides', 'short_name' => 'Sans pesticides', 'type' => 'no_pesticide',
                'issuer' => 'Laboratoire d\'analyses indépendant (exemple)',
                'expires_at' => now()->addMonths(5)->toDateString(),
                'description' => 'Analyses en laboratoire confirmant l\'absence de résidus de pesticides détectables sur le produit fini.',
                'criteria' => ['Analyse de plus de 300 molécules', 'Seuil de quantification de 0,01 mg/kg', 'Analyse à chaque lot'],
                'guarantees' => ['Produit fini sans résidus détectables'],
                'limits' => ['Ne dit rien des pratiques au champ ni du sol'],
            ],
            [
                'slug' => 'agriculture-raisonnee', 'name' => 'Agriculture raisonnée', 'short_name' => 'Raisonnée', 'type' => 'reasoned',
                'issuer' => 'Référentiel national de l\'agriculture raisonnée (exemple)',
                'expires_at' => now()->subMonths(2)->toDateString(), // expired: shown as such on product pages
                'description' => 'Encadre l\'usage des intrants : traitements limités au strict nécessaire et justifiés.',
                'criteria' => ['Traitements justifiés et enregistrés', 'Gestion raisonnée de l\'eau', 'Formation des exploitants'],
                'guarantees' => ['Usage limité et tracé des traitements'],
                'limits' => ['Autorise les pesticides de synthèse', 'Souvent confondu à tort avec le bio'],
            ],
        ];

        foreach ($certifications as $i => $certification) {
            Certification::factory()->create($certification + ['created_at' => now()->subMonths(11 + $i)]);
        }
    }
}
