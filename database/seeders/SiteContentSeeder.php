<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['Comment retrouver l\'histoire d\'un produit ?', 'Saisissez le numéro de lot imprimé sur l\'emballage (ex. NT-2026-OLV-0412) dans la recherche « Tracer un lot », ou scannez le QR code : chaque étape, de la ferme au rayon, s\'affiche avec ses justificatifs.'],
            ['Qui vérifie les certifications affichées ?', 'Chaque certificat transmis par un acteur est contrôlé par l\'équipe de modération (numéro, organisme, date de validité) avant d\'apparaître avec le badge « Vérifié ».'],
            ['Comment est calculé l\'éco-score ?', 'Il combine cinq indicateurs : émissions de CO₂e par kg, consommation d\'eau, distance parcourue, type d\'emballage et saisonnalité. Le score sur 100 est converti en note de A à E.'],
            ['Que se passe-t-il quand je signale un produit ?', 'Votre signalement reçoit une référence de suivi. Un modérateur l\'examine, contacte l\'acteur concerné et publie une décision. Les cas fondés apparaissent, anonymisés, dans l\'Observatoire.'],
            ['Mes avis sont-ils publiés immédiatement ?', 'Les avis sont publiés après une vérification automatique. Ceux qui contiennent des allégations sensibles passent par une modération humaine.'],
            ['Je suis producteur : comment rejoindre NutriTrace ?', 'Créez un compte, puis complétez votre profil acteur et déposez vos certificats. Notre équipe vous accompagne pour tracer vos premiers lots.'],
        ];

        foreach ($faqs as $i => [$question, $answer]) {
            Faq::factory()->create(['question' => $question, 'answer' => $answer, 'position' => $i + 1]);
        }

        Testimonial::factory()->createMany([
            ['name' => 'Yasmine B.', 'role' => 'Consommatrice, Tunis', 'quote' => 'Je scanne le QR code en magasin et je vois le moulin, la date de récolte et les kilomètres parcourus. Je ne fais plus mes courses autrement.', 'rating' => 5],
            ['name' => 'Hédi M.', 'role' => 'Oléiculteur, Sfax', 'quote' => 'NutriTrace valorise notre travail : nos clients voient enfin la différence entre une huile tracée et une huile anonyme.', 'rating' => 5],
            ['name' => 'Salma J.', 'role' => 'Consommatrice, Sousse', 'quote' => 'J\'ai signalé une allégation « zéro impact » douteuse. Elle a été retirée en deux semaines. On se sent écoutée.', 'rating' => 5],
            ['name' => 'Fatma K.', 'role' => 'Coopérative de dattes, Tozeur', 'quote' => 'Les avis des clients nous aident à améliorer nos emballages, et le label équitable est enfin compris.', 'rating' => 4],
        ]);
    }
}
