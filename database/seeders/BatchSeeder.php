<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Batch;
use App\Models\BatchStep;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::pluck('id', 'slug');
        $actors = Actor::pluck('id', 'slug');

        // code, product, quantity, status, steps: [stage, actor, title, location, days ago, action, documents, km]
        $batches = [
            ['NT-2026-OLV-0412', 'huile-olive-sfax', '1 200 bouteilles', 'delivered', [
                ['production', 'domaine-zitouna', 'Récolte des olives', 'Oliveraie Zitouna, Sfax', 52, 'Récolte manuelle de 18 t d\'olives Chemlali à maturité.', ['Bon de récolte', 'Certificat bio 2026'], 0],
                ['processing', 'huilerie-el-baraka', 'Trituration à froid', 'Huilerie El Baraka, Sfax', 51, 'Extraction à froid (< 27 °C) en moins de 24 h.', ['Rapport d\'analyse acidité 0,3 %'], 12],
                ['processing', 'huilerie-el-baraka', 'Mise en bouteille', 'Huilerie El Baraka, Sfax', 40, 'Filtration douce et embouteillage sous azote.', ['Fiche de conditionnement'], 0],
                ['distribution', 'souk-ennour', 'Stockage & expédition', 'Plateforme Souk Ennour, Sousse', 35, 'Réception, contrôle qualité et préparation des commandes.', ['Bon de livraison BL-8812'], 128],
                ['distribution', 'marche-bio-tunis', 'Mise en rayon', 'Marché Bio Tunis', 31, 'Mise en vente dans les trois magasins du réseau.', ['Bon de réception'], 140],
                ['consumer', null, 'Vente au consommateur', 'Tunis', 20, 'Lot entièrement vendu.', [], 0],
            ]],
            ['NT-2026-DAT-0187', 'dattes-deglet-nour-tozeur', '2 400 barquettes', 'delivered', [
                ['production', 'oasis-nakhla', 'Récolte en régime', 'Oasis Nakhla, Tozeur', 70, 'Cueillette des régimes par les grimpeurs de la coopérative.', ['Registre de récolte'], 0],
                ['production', 'oasis-nakhla', 'Tri & calibrage', 'Station Nakhla, Tozeur', 68, 'Tri manuel, désinsectisation par le froid, sans fumigation chimique.', ['Certificat équitable', 'Fiche de tri'], 3],
                ['distribution', 'souk-ennour', 'Transport réfrigéré', 'Plateforme Souk Ennour, Sousse', 63, 'Transport à 4 °C vers la plateforme régionale.', ['Relevé de température'], 290],
                ['distribution', 'epicerie-verte', 'Vente en vrac', 'Épicerie Verte La Marsa', 58, 'Vente en vrac et en barquettes compostables.', ['Bon de réception'], 137],
                ['consumer', null, 'Vente au consommateur', 'La Marsa', 40, 'Lot vendu à 96 %.', [], 0],
            ]],
            ['NT-2026-HAR-0058', 'harissa-cap-bon', '3 000 bocaux', 'in_transit', [
                ['production', 'ferme-ennahl', 'Récolte des piments', 'Ferme Ennahl, Menzel Temime', 30, 'Récolte des piments Baklouti en fin d\'été.', ['Bon de récolte'], 0],
                ['processing', 'conserverie-cap-bon', 'Séchage au soleil', 'Conserverie Cap Bon Saveurs, Korba', 26, 'Séchage 10 jours sur les terrasses.', ['Fiche de séchage'], 24],
                ['processing', 'conserverie-cap-bon', 'Broyage & mise en bocal', 'Conserverie Cap Bon Saveurs, Korba', 12, 'Broyage à la meule, mélange des épices, pasteurisation.', ['Analyse microbiologique'], 0],
                ['distribution', 'souk-ennour', 'Expédition', 'Plateforme Souk Ennour, Sousse', 4, 'En cours d\'acheminement vers les points de vente.', ['Bon de transport BT-0911'], 115],
            ]],
            ['NT-2026-ORA-0321', 'oranges-maltaises-nabeul', '850 cagettes', 'delivered', [
                ['production', 'ferme-ennahl', 'Cueillette', 'Vergers Ennahl, Menzel Temime', 18, 'Cueillette à maturité, sans traitement post-récolte.', ['Bon de récolte', 'Attestation local'], 0],
                ['distribution', 'epicerie-verte', 'Livraison directe', 'Épicerie Verte La Marsa', 17, 'Livraison directe producteur → épicerie (circuit court).', ['Bon de livraison'], 65],
                ['consumer', null, 'Vente au consommateur', 'La Marsa', 10, 'Vente en cagettes et au détail.', [], 0],
            ]],
            ['NT-2026-MIE-0009', 'miel-thym-zaghouan', '600 pots', 'in_production', [
                ['production', 'bergerie-djebel', 'Récolte des hausses', 'Ruchers du Djebel, Zaghouan', 9, 'Récolte des cadres operculés, extraction par centrifugation.', ['Carnet de miellerie'], 0],
                ['production', 'bergerie-djebel', 'Maturation', 'Miellerie du Djebel, Zaghouan', 5, 'Maturation en cuve 15 jours avant mise en pot.', [], 2],
            ]],
            ['NT-2026-CHV-0144', 'fromage-chevre-zaghouan', '1 100 pièces', 'delivered', [
                ['production', 'bergerie-djebel', 'Traite & caillage', 'Bergerie du Djebel, Zaghouan', 45, 'Traite du matin, caillage lactique 24 h.', ['Registre sanitaire du troupeau'], 0],
                ['processing', 'bergerie-djebel', 'Affinage', 'Cave d\'affinage, Zaghouan', 44, 'Affinage trois semaines en cave naturelle.', ['Relevé hygrométrie'], 1],
                ['distribution', 'marche-bio-tunis', 'Livraison réfrigérée', 'Marché Bio Tunis', 22, 'Livraison sous froid positif.', ['Relevé de température'], 60],
                ['consumer', null, 'Vente au consommateur', 'Tunis', 12, 'Lot vendu.', [], 0],
            ]],
        ];

        foreach ($batches as [$code, $product, $quantity, $status, $steps]) {
            $productionDate = now()->subDays($steps[0][4])->setTime(9, 30);

            $batch = Batch::factory()->create([
                'code' => $code,
                'product_id' => $products[$product],
                'quantity' => $quantity,
                'status' => $status,
                'production_date' => $productionDate,
                'created_at' => $productionDate,
            ]);

            foreach ($steps as $i => [$stage, $actor, $title, $location, $daysAgo, $action, $documents, $km]) {
                BatchStep::factory()->for($batch)->create([
                    'actor_id' => $actor ? $actors[$actor] : null,
                    'position' => $i + 1,
                    'stage' => $stage,
                    'title' => $title,
                    'location' => $location,
                    'date' => now()->subDays($daysAgo)->setTime(9 + $i, 30),
                    'action' => $action,
                    'documents' => $documents,
                    'distance_km' => $km,
                    // The last step of the harissa lot is still waiting for its proof of shipment.
                    'verified' => $stage !== 'consumer' && ! ($code === 'NT-2026-HAR-0058' && $i === 3),
                ]);
            }
        }
    }
}
