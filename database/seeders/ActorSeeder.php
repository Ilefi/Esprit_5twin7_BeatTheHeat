<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Certification;
use Illuminate\Database\Seeder;

class ActorSeeder extends Seeder
{
    public function run(): void
    {
        // slug, name, type, city, region, description, founded, certification slugs
        $actors = [
            ['domaine-zitouna', 'Domaine Zitouna', 'producer', 'Sfax', 'Sfax', 'Oliveraie familiale de 40 hectares, conduite en agriculture biologique depuis 2014.', 1998, ['bio', 'origine-protegee']],
            ['oasis-nakhla', 'Oasis Nakhla', 'producer', 'Tozeur', 'Tozeur', 'Coopérative de 26 phœniciculteurs cultivant la Deglet Nour en étages, sous les palmiers.', 2006, ['equitable', 'origine-protegee']],
            ['ferme-ennahl', 'Ferme Ennahl', 'producer', 'Menzel Temime', 'Nabeul', 'Exploitation maraîchère et agrumicole du Cap Bon, irrigation goutte-à-goutte.', 2011, ['local', 'agriculture-raisonnee']],
            ['bergerie-djebel', 'Bergerie du Djebel', 'producer', 'Zaghouan', 'Zaghouan', 'Élevage caprin, ruchers de thym sauvage et parcelles de blé dur sur les flancs du djebel.', 2003, ['local']],
            ['huilerie-el-baraka', 'Huilerie El Baraka', 'processor', 'Sfax', 'Sfax', 'Moulin moderne à extraction à froid, trituration sous 24 h après récolte.', 1987, ['bio']],
            ['conserverie-cap-bon', 'Conserverie Cap Bon Saveurs', 'processor', 'Korba', 'Nabeul', 'Atelier artisanal de harissa et de légumes séchés au soleil.', 2009, ['local']],
            ['moulin-essafi', 'Moulin Essafi', 'processor', 'Béja', 'Béja', 'Semoulerie et conditionnement de légumineuses du Nord-Ouest.', 1979, ['agriculture-raisonnee']],
            ['marche-bio-tunis', 'Marché Bio Tunis', 'distributor', 'Tunis', 'Tunis', 'Réseau de trois magasins spécialisés en produits biologiques et locaux.', 2015, ['bio']],
            ['epicerie-verte', 'Épicerie Verte La Marsa', 'distributor', 'La Marsa', 'Tunis', 'Épicerie vrac zéro déchet, partenaire direct de 30 producteurs.', 2019, ['local']],
            ['souk-ennour', 'Coopérative Souk Ennour', 'distributor', 'Sousse', 'Sousse', 'Plateforme logistique coopérative pour le Sahel et le Centre-Est.', 2012, []],
        ];

        foreach ($actors as $i => [$slug, $name, $type, $city, $region, $description, $founded, $certifications]) {
            $actor = Actor::factory()->create([
                'slug' => $slug,
                'name' => $name,
                'type' => $type,
                'city' => $city,
                'region' => $region,
                'description' => $description,
                'founded_year' => $founded,
                'verified' => $slug !== 'souk-ennour',
                'email' => 'contact@'.$slug.'.tn.example',
                'phone' => '+216 7'.($i + 1).' 000 '.str_pad((string) (($i + 1) * 37), 3, '0', STR_PAD_LEFT),
                'created_at' => now()->subMonths(13 - $i),
            ]);

            $actor->certifications()->attach(Certification::whereIn('slug', $certifications)->pluck('id'));
        }
    }
}
