<?php

namespace App\Support;

use App\View\Components\StatusBadge;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * In-memory demo dataset used by the UI template until each module ships its Eloquent models.
 *
 * Records are plain objects with Eloquent-like snake_case attributes and nested relations
 * ($product->certifications, $product->impact, $batch->steps, $report->target…), so replacing
 * a DemoData call with a query only touches the controllers. All names are fictional.
 */
class DemoData
{
    /** @var array<string, Collection> */
    private static array $cache = [];

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public static function categories(): Collection
    {
        return self::remember('categories', function () {
            return collect([
                [1, 'Huiles & condiments', 'huiles-condiments', 'fa-bottle-droplet'],
                [2, 'Fruits', 'fruits', 'fa-apple-whole'],
                [3, 'Fruits secs', 'fruits-secs', 'fa-seedling'],
                [4, 'Épicerie & céréales', 'epicerie-cereales', 'fa-wheat-awn'],
                [5, 'Produits de la ruche', 'produits-ruche', 'fa-jar'],
                [6, 'Produits laitiers', 'produits-laitiers', 'fa-cheese'],
            ])->map(fn ($c) => self::make([
                'id' => $c[0], 'name' => $c[1], 'slug' => $c[2], 'icon' => $c[3], 'products_count' => 0,
            ]));
        });
    }

    public static function certifications(): Collection
    {
        return self::remember('certifications', function () {
            return collect([
                [
                    'id' => 1, 'slug' => 'bio', 'name' => 'Agriculture biologique', 'short_name' => 'Bio', 'type' => 'bio',
                    'issuer' => 'Organisme de contrôle bio agréé (exemple)',
                    'description' => 'Garantit une production sans pesticides ni engrais chimiques de synthèse, sans OGM, avec un contrôle annuel sur site.',
                    'criteria' => ['Aucun intrant chimique de synthèse', 'Période de conversion de 2 à 3 ans', 'Rotation des cultures', 'Contrôle annuel + contrôles inopinés'],
                    'guarantees' => ['Mode de production contrôlé', 'Absence d\'OGM', 'Traçabilité documentaire'],
                    'limits' => ['Ne garantit pas une origine locale', 'Ne mesure pas l\'empreinte carbone du transport'],
                ],
                [
                    'id' => 2, 'slug' => 'local', 'name' => 'Produit local', 'short_name' => 'Local', 'type' => 'local',
                    'issuer' => 'Charte régionale des circuits courts (exemple)',
                    'description' => 'Produit cultivé et transformé à moins de 250 km du point de vente, avec au plus un intermédiaire.',
                    'criteria' => ['Moins de 250 km entre champ et rayon', 'Un intermédiaire maximum', 'Origine déclarée et vérifiable'],
                    'guarantees' => ['Proximité géographique', 'Transport réduit'],
                    'limits' => ['Ne garantit pas un mode de culture biologique'],
                ],
                [
                    'id' => 3, 'slug' => 'equitable', 'name' => 'Commerce équitable', 'short_name' => 'Équitable', 'type' => 'fair',
                    'issuer' => 'Réseau du commerce équitable (exemple)',
                    'description' => 'Assure un prix minimum garanti aux producteurs et une prime de développement pour la coopérative.',
                    'criteria' => ['Prix minimum garanti', 'Contrats pluriannuels', 'Prime de développement', 'Gouvernance démocratique'],
                    'guarantees' => ['Rémunération juste du producteur', 'Relation commerciale durable'],
                    'limits' => ['Ne certifie pas à lui seul le mode de culture'],
                ],
                [
                    'id' => 4, 'slug' => 'origine-protegee', 'name' => 'Origine protégée', 'short_name' => 'Origine', 'type' => 'origin',
                    'issuer' => 'Comité des appellations d\'origine (exemple)',
                    'description' => 'Lie le produit à un terroir précis et à un savoir-faire traditionnel décrit dans un cahier des charges.',
                    'criteria' => ['Zone géographique délimitée', 'Cahier des charges de production', 'Contrôle par un organisme tiers'],
                    'guarantees' => ['Origine géographique', 'Savoir-faire traditionnel'],
                    'limits' => ['Ne garantit pas l\'absence de pesticides'],
                ],
                [
                    'id' => 5, 'slug' => 'sans-pesticides', 'name' => 'Sans résidus de pesticides', 'short_name' => 'Sans pesticides', 'type' => 'no_pesticide',
                    'issuer' => 'Laboratoire d\'analyses indépendant (exemple)',
                    'description' => 'Analyses en laboratoire confirmant l\'absence de résidus de pesticides détectables sur le produit fini.',
                    'criteria' => ['Analyse de plus de 300 molécules', 'Seuil de quantification de 0,01 mg/kg', 'Analyse à chaque lot'],
                    'guarantees' => ['Produit fini sans résidus détectables'],
                    'limits' => ['Ne dit rien des pratiques au champ ni du sol'],
                ],
                [
                    'id' => 6, 'slug' => 'agriculture-raisonnee', 'name' => 'Agriculture raisonnée', 'short_name' => 'Raisonnée', 'type' => 'reasoned',
                    'issuer' => 'Référentiel national de l\'agriculture raisonnée (exemple)',
                    'description' => 'Encadre l\'usage des intrants : traitements limités au strict nécessaire et justifiés.',
                    'criteria' => ['Traitements justifiés et enregistrés', 'Gestion raisonnée de l\'eau', 'Formation des exploitants'],
                    'guarantees' => ['Usage limité et tracé des traitements'],
                    'limits' => ['Autorise les pesticides de synthèse', 'Souvent confondu à tort avec le bio'],
                ],
            ])->map(fn ($c) => self::make($c + ['products_count' => 0, 'actors_count' => 0, 'created_at' => Carbon::now()->subMonths(10 + $c['id'])]));
        });
    }

    public static function actors(): Collection
    {
        return self::remember('actors', function () {
            $certs = self::certifications()->keyBy('slug');

            return collect([
                [1, 'domaine-zitouna', 'Domaine Zitouna', 'producer', 'Sfax', 'Sfax', 'Oliveraie familiale de 40 hectares, conduite en agriculture biologique depuis 2014.', 1998, ['bio', 'origine-protegee']],
                [2, 'oasis-nakhla', 'Oasis Nakhla', 'producer', 'Tozeur', 'Tozeur', 'Coopérative de 26 phœniciculteurs cultivant la Deglet Nour en étages, sous les palmiers.', 2006, ['equitable', 'origine-protegee']],
                [3, 'ferme-ennahl', 'Ferme Ennahl', 'producer', 'Menzel Temime', 'Nabeul', 'Exploitation maraîchère et agrumicole du Cap Bon, irrigation goutte-à-goutte.', 2011, ['local', 'agriculture-raisonnee']],
                [4, 'bergerie-djebel', 'Bergerie du Djebel', 'producer', 'Zaghouan', 'Zaghouan', 'Élevage caprin, ruchers de thym sauvage et parcelles de blé dur sur les flancs du djebel.', 2003, ['local']],
                [5, 'huilerie-el-baraka', 'Huilerie El Baraka', 'processor', 'Sfax', 'Sfax', 'Moulin moderne à extraction à froid, trituration sous 24 h après récolte.', 1987, ['bio']],
                [6, 'conserverie-cap-bon', 'Conserverie Cap Bon Saveurs', 'processor', 'Korba', 'Nabeul', 'Atelier artisanal de harissa et de légumes séchés au soleil.', 2009, ['local']],
                [7, 'moulin-essafi', 'Moulin Essafi', 'processor', 'Béja', 'Béja', 'Semoulerie et conditionnement de légumineuses du Nord-Ouest.', 1979, ['agriculture-raisonnee']],
                [8, 'marche-bio-tunis', 'Marché Bio Tunis', 'distributor', 'Tunis', 'Tunis', 'Réseau de trois magasins spécialisés en produits biologiques et locaux.', 2015, ['bio']],
                [9, 'epicerie-verte', 'Épicerie Verte La Marsa', 'distributor', 'La Marsa', 'Tunis', 'Épicerie vrac zéro déchet, partenaire direct de 30 producteurs.', 2019, ['local']],
                [10, 'souk-ennour', 'Coopérative Souk Ennour', 'distributor', 'Sousse', 'Sousse', 'Plateforme logistique coopérative pour le Sahel et le Centre-Est.', 2012, []],
            ])->map(fn ($a) => self::make([
                'id' => $a[0], 'slug' => $a[1], 'name' => $a[2], 'type' => $a[3], 'city' => $a[4], 'region' => $a[5],
                'description' => $a[6], 'founded_year' => $a[7],
                'certifications' => collect($a[8])->map(fn ($slug) => $certs[$slug])->values(),
                'verified' => $a[0] !== 10,
                'email' => 'contact@'.$a[1].'.tn.example',
                'phone' => '+216 7'.$a[0].' 000 '.str_pad((string) ($a[0] * 37), 3, '0', STR_PAD_LEFT),
                'products_count' => 0,
                'batches_count' => 0,
                'created_at' => Carbon::now()->subMonths(14 - $a[0]),
            ]));
        });
    }

    public static function products(): Collection
    {
        if (isset(self::$cache['products'])) {
            return self::$cache['products'];
        }

        $categories = self::categories()->keyBy('id');
        $actors = self::actors()->keyBy('id');
        $certs = self::certifications()->keyBy('slug');

        // id, slug, name, category, producer, processor, region, format, price, certifications, impact [co2, water, km, packaging, seasonal, breakdown]
        $rows = [
            [1, 'huile-olive-sfax', 'Huile d\'olive extra vierge de Sfax', 1, 1, 5, 'Sfax', 'Bouteille verre 75 cl', 32.5, ['bio', 'origine-protegee'], [2.4, 1100, 280, 'recyclable', true, [62, 21, 9, 8]],
                'Huile fruitée vert issue de la variété Chemlali, extraite à froid moins de 24 heures après la récolte.', 'Huile d\'olive vierge extra 100 % Chemlali.'],
            [2, 'dattes-deglet-nour-tozeur', 'Dattes Deglet Nour de Tozeur', 2, 2, null, 'Tozeur', 'Barquette carton 500 g', 9.8, ['equitable', 'origine-protegee'], [1.1, 2200, 430, 'compostable', true, [48, 6, 38, 8]],
                'Dattes branchées translucides au goût de miel, récoltées à la main dans l\'oasis de Tozeur.', 'Dattes Deglet Nour 100 %, sans sirop de glucose ajouté.'],
            [3, 'harissa-cap-bon', 'Harissa traditionnelle du Cap Bon', 1, 3, 6, 'Nabeul', 'Bocal verre 200 g', 6.4, ['local'], [1.6, 600, 70, 'recyclable', false, [40, 35, 5, 20]],
                'Piments Baklouti séchés au soleil, broyés à la meule avec ail, carvi et coriandre.', 'Piments 78 %, huile d\'olive, ail, sel, carvi, coriandre.'],
            [4, 'oranges-maltaises-nabeul', 'Oranges maltaises de Nabeul', 2, 3, null, 'Nabeul', 'Cagette bois 3 kg', 7.9, ['local', 'agriculture-raisonnee'], [0.4, 450, 65, 'compostable', true, [70, 0, 22, 8]],
                'La « blonde » du Cap Bon, juteuse et peu acide, cueillie à maturité en pleine saison.', 'Oranges maltaises demi-sanguines, non traitées après récolte.'],
            [5, 'miel-thym-zaghouan', 'Miel de thym du Djebel Zaghouan', 5, 4, null, 'Zaghouan', 'Pot verre 250 g', 24.0, ['local'], [1.2, 200, 60, 'recyclable', false, [55, 10, 5, 30]],
                'Miel ambré aux notes puissantes, butiné sur le thym sauvage des pentes du djebel.', 'Miel de thym 100 %, non chauffé, non filtré.'],
            [6, 'figues-djebba', 'Figues fraîches de l\'oasis', 2, 2, null, 'Tozeur', 'Barquette carton 400 g', 8.5, ['equitable'], [0.6, 900, 120, 'compostable', true, [72, 0, 20, 8]],
                'Figues violettes cueillies le matin même, à la chair fondante et sucrée.', 'Figues fraîches 100 %.'],
            [7, 'amandes-sfax', 'Amandes décortiquées de Sfax', 3, 1, 5, 'Sfax', 'Sachet kraft doublé 250 g', 14.9, ['bio'], [2.1, 8000, 280, 'mixed', false, [58, 14, 10, 18]],
                'Amandes de variété locale, cultivées en sec puis décortiquées mécaniquement.', 'Amandes 100 %.'],
            [8, 'couscous-complet', 'Couscous complet au blé dur', 4, 4, 7, 'Béja', 'Sachet plastique 1 kg', 4.6, ['agriculture-raisonnee'], [1.4, 1600, 150, 'plastic', false, [52, 28, 8, 12]],
                'Couscous roulé à partir de blé dur complet, séché lentement à basse température.', 'Semoule de blé dur complet, eau.'],
            [9, 'pois-chiches-beja', 'Pois chiches du Nord-Ouest', 4, 3, 7, 'Béja', 'Sachet plastique 500 g', 3.9, ['local'], [0.9, 1200, 210, 'plastic', false, [61, 10, 13, 16]],
                'Pois chiches de petit calibre, tendres à la cuisson, cultivés en rotation avec le blé.', 'Pois chiches secs 100 %.'],
            [10, 'tomates-sechees', 'Tomates séchées au soleil', 1, 3, 6, 'Nabeul', 'Sachet souple 150 g', 7.2, ['sans-pesticides'], [3.8, 1900, 900, 'mixed', false, [38, 30, 22, 10]],
                'Tomates allongées séchées sur claies, puis réhydratées à l\'huile d\'olive.', 'Tomates 92 %, huile d\'olive, sel, origan.'],
            [11, 'fromage-chevre-zaghouan', 'Fromage de chèvre affiné', 6, 4, null, 'Zaghouan', 'Barquette plastique 180 g', 12.0, ['local'], [8.5, 3500, 180, 'plastic', true, [80, 8, 4, 8]],
                'Petit fromage lactique au lait cru de chèvre, affiné trois semaines.', 'Lait cru de chèvre, sel, ferments lactiques, présure.'],
            [12, 'sel-marin-sfax', 'Fleur de sel des salines de Sfax', 1, 1, null, 'Sfax', 'Sachet papier 250 g', 5.5, ['origine-protegee'], [0.3, 10, 1800, 'recyclable', false, [20, 5, 70, 5]],
                'Cristaux récoltés à la main à la surface des bassins, séchés au soleil et au vent.', 'Fleur de sel marin 100 %, non raffinée.'],
        ];

        $products = collect($rows)->map(function ($p) use ($categories, $actors, $certs) {
            [$co2, $water, $km, $packaging, $seasonal, $breakdown] = $p[10];
            $points = EcoScore::points($co2, $water, $km, $packaging, $seasonal);
            $grade = EcoScore::grade($points);

            $impact = self::make([
                'id' => $p[0], 'product_id' => $p[0],
                'co2_per_kg' => $co2, 'water_per_kg' => $water, 'distance_km' => $km,
                'packaging' => $packaging, 'seasonal' => $seasonal,
                'eco_points' => $points, 'eco_score' => $grade,
                'breakdown' => array_combine(['Production', 'Transformation', 'Transport', 'Emballage'], $breakdown),
                'updated_at' => Carbon::now()->subDays(3 * $p[0]),
            ]);

            return self::make([
                'id' => $p[0], 'slug' => $p[1], 'name' => $p[2],
                'category' => $categories[$p[3]],
                'producer' => $actors[$p[4]],
                'processor' => $p[5] ? $actors[$p[5]] : null,
                'region' => $p[6], 'format' => $p[7], 'price' => $p[8],
                'certifications' => collect($p[9])->map(fn ($slug) => $certs[$slug])->values(),
                'impact' => $impact,
                'eco_score' => $grade,
                'description' => $p[11], 'composition' => $p[12],
                'image' => null,
                'status' => match ($p[0]) {
                    9 => 'draft', 10 => 'pending', default => 'published'
                },
                'rating_avg' => 0, 'reviews_count' => 0,
                'batch_code' => null,
                'created_at' => Carbon::now()->subDays(40 + $p[0] * 9),
            ]);
        });

        self::$cache['products'] = $products;

        // Counters on related records.
        foreach ($products as $product) {
            $product->category->products_count++;
            $product->producer->products_count++;
            foreach ($product->certifications as $cert) {
                $cert->products_count++;
            }
        }
        foreach (self::actors() as $actor) {
            foreach ($actor->certifications as $cert) {
                $cert->actors_count++;
            }
        }

        foreach (self::batches() as $batch) {
            $batch->product->batch_code ??= $batch->code;
        }

        foreach (self::reviews()->where('status', 'published')->groupBy(fn ($r) => $r->product->id) as $productId => $reviews) {
            $product = $products->firstWhere('id', $productId);
            $product->reviews_count = $reviews->count();
            $product->rating_avg = round($reviews->avg('rating'), 1);
        }

        return $products;
    }

    public static function batches(): Collection
    {
        if (isset(self::$cache['batches'])) {
            return self::$cache['batches'];
        }

        $products = (self::$cache['products'] ?? self::products())->keyBy('id');

        if (isset(self::$cache['batches'])) {
            return self::$cache['batches'];
        }

        $actors = self::actors()->keyBy('id');

        // code, product, quantity, status, steps: [stage, actor, title, location, days ago, action, documents, km]
        $rows = [
            ['NT-2026-OLV-0412', 1, '1 200 bouteilles', 'delivered', [
                ['production', 1, 'Récolte des olives', 'Oliveraie Zitouna, Sfax', 52, 'Récolte manuelle de 18 t d\'olives Chemlali à maturité.', ['Bon de récolte', 'Certificat bio 2026'], 0],
                ['processing', 5, 'Trituration à froid', 'Huilerie El Baraka, Sfax', 51, 'Extraction à froid (< 27 °C) en moins de 24 h.', ['Rapport d\'analyse acidité 0,3 %'], 12],
                ['processing', 5, 'Mise en bouteille', 'Huilerie El Baraka, Sfax', 40, 'Filtration douce et embouteillage sous azote.', ['Fiche de conditionnement'], 0],
                ['distribution', 10, 'Stockage & expédition', 'Plateforme Souk Ennour, Sousse', 35, 'Réception, contrôle qualité et préparation des commandes.', ['Bon de livraison BL-8812'], 128],
                ['distribution', 8, 'Mise en rayon', 'Marché Bio Tunis', 31, 'Mise en vente dans les trois magasins du réseau.', ['Bon de réception'], 140],
                ['consumer', null, 'Vente au consommateur', 'Tunis', 20, 'Lot entièrement vendu.', [], 0],
            ]],
            ['NT-2026-DAT-0187', 2, '2 400 barquettes', 'delivered', [
                ['production', 2, 'Récolte en régime', 'Oasis Nakhla, Tozeur', 70, 'Cueillette des régimes par les grimpeurs de la coopérative.', ['Registre de récolte'], 0],
                ['production', 2, 'Tri & calibrage', 'Station Nakhla, Tozeur', 68, 'Tri manuel, désinsectisation par le froid, sans fumigation chimique.', ['Certificat équitable', 'Fiche de tri'], 3],
                ['distribution', 10, 'Transport réfrigéré', 'Plateforme Souk Ennour, Sousse', 63, 'Transport à 4 °C vers la plateforme régionale.', ['Relevé de température'], 290],
                ['distribution', 9, 'Vente en vrac', 'Épicerie Verte La Marsa', 58, 'Vente en vrac et en barquettes compostables.', ['Bon de réception'], 137],
                ['consumer', null, 'Vente au consommateur', 'La Marsa', 40, 'Lot vendu à 96 %.', [], 0],
            ]],
            ['NT-2026-HAR-0058', 3, '3 000 bocaux', 'in_transit', [
                ['production', 3, 'Récolte des piments', 'Ferme Ennahl, Menzel Temime', 30, 'Récolte des piments Baklouti en fin d\'été.', ['Bon de récolte'], 0],
                ['processing', 6, 'Séchage au soleil', 'Conserverie Cap Bon Saveurs, Korba', 26, 'Séchage 10 jours sur les terrasses.', ['Fiche de séchage'], 24],
                ['processing', 6, 'Broyage & mise en bocal', 'Conserverie Cap Bon Saveurs, Korba', 12, 'Broyage à la meule, mélange des épices, pasteurisation.', ['Analyse microbiologique'], 0],
                ['distribution', 10, 'Expédition', 'Plateforme Souk Ennour, Sousse', 4, 'En cours d\'acheminement vers les points de vente.', ['Bon de transport BT-0911'], 115],
            ]],
            ['NT-2026-ORA-0321', 4, '850 cagettes', 'delivered', [
                ['production', 3, 'Cueillette', 'Vergers Ennahl, Menzel Temime', 18, 'Cueillette à maturité, sans traitement post-récolte.', ['Bon de récolte', 'Attestation local'], 0],
                ['distribution', 9, 'Livraison directe', 'Épicerie Verte La Marsa', 17, 'Livraison directe producteur → épicerie (circuit court).', ['Bon de livraison'], 65],
                ['consumer', null, 'Vente au consommateur', 'La Marsa', 10, 'Vente en cagettes et au détail.', [], 0],
            ]],
            ['NT-2026-MIE-0009', 5, '600 pots', 'in_production', [
                ['production', 4, 'Récolte des hausses', 'Ruchers du Djebel, Zaghouan', 9, 'Récolte des cadres operculés, extraction par centrifugation.', ['Carnet de miellerie'], 0],
                ['production', 4, 'Maturation', 'Miellerie du Djebel, Zaghouan', 5, 'Maturation en cuve 15 jours avant mise en pot.', [], 2],
            ]],
            ['NT-2026-CHV-0144', 11, '1 100 pièces', 'delivered', [
                ['production', 4, 'Traite & caillage', 'Bergerie du Djebel, Zaghouan', 45, 'Traite du matin, caillage lactique 24 h.', ['Registre sanitaire du troupeau'], 0],
                ['processing', 4, 'Affinage', 'Cave d\'affinage, Zaghouan', 44, 'Affinage trois semaines en cave naturelle.', ['Relevé hygrométrie'], 1],
                ['distribution', 8, 'Livraison réfrigérée', 'Marché Bio Tunis', 22, 'Livraison sous froid positif.', ['Relevé de température'], 60],
                ['consumer', null, 'Vente au consommateur', 'Tunis', 12, 'Lot vendu.', [], 0],
            ]],
        ];

        $batches = collect($rows)->values()->map(function ($b, $index) use ($products, $actors) {
            $steps = collect($b[4])->values()->map(fn ($s, $i) => self::make([
                'id' => ($index + 1) * 10 + $i,
                'position' => $i + 1,
                'stage' => $s[0],
                'actor' => $s[1] ? $actors[$s[1]] : null,
                'title' => $s[2],
                'location' => $s[3],
                'date' => Carbon::now()->subDays($s[4])->setTime(9 + $i, 30),
                'action' => $s[5],
                'documents' => $s[6],
                'distance_km' => $s[7],
                'verified' => $s[0] !== 'consumer' && ! ($index === 2 && $i === 3),
            ]));

            return self::make([
                'id' => $index + 1,
                'code' => $b[0],
                'product' => $products[$b[1]],
                'quantity' => $b[2],
                'status' => $b[3],
                'steps' => $steps,
                'total_km' => $steps->sum('distance_km'),
                'actors_count' => $steps->pluck('actor')->filter()->unique('id')->count(),
                'production_date' => $steps->first()->date,
                'created_at' => $steps->first()->date,
            ]);
        });

        self::$cache['batches'] = $batches;

        foreach ($batches as $batch) {
            foreach ($batch->steps->pluck('actor')->filter()->unique('id') as $actor) {
                $actor->batches_count++;
            }
        }

        return $batches;
    }

    public static function users(): Collection
    {
        return self::remember('users', function () {
            return collect([
                [1, 'Amira Ben Salah', 'admin@nutritrace.tn', 'admin'],
                [2, 'Karim Trabelsi', 'actor@nutritrace.tn', 'actor'],
                [3, 'Yasmine Bouaziz', 'consumer@nutritrace.tn', 'consumer'],
                [4, 'Mehdi Gharbi', 'mehdi.g@example.tn', 'consumer'],
                [5, 'Salma Jebali', 'salma.j@example.tn', 'consumer'],
                [6, 'Nizar Hammami', 'nizar.h@example.tn', 'actor'],
                [7, 'Ines Mansouri', 'ines.m@example.tn', 'consumer'],
                [8, 'Walid Chaabane', 'walid.c@example.tn', 'consumer'],
                [9, 'Rim Ferchichi', 'rim.f@example.tn', 'admin'],
                [10, 'Oussama Belhadj', 'oussama.b@example.tn', 'consumer'],
            ])->map(fn ($u) => self::make([
                'id' => $u[0], 'name' => $u[1], 'email' => $u[2], 'role' => $u[3],
                'created_at' => Carbon::now()->subDays(300 - $u[0] * 23),
                'last_login_at' => Carbon::now()->subHours($u[0] * 7),
                'reviews_count' => 0, 'reports_count' => 0,
            ]));
        });
    }

    public static function reviews(): Collection
    {
        if (isset(self::$cache['reviews'])) {
            return self::$cache['reviews'];
        }

        $products = (self::$cache['products'] ?? self::products())->keyBy('id');

        if (isset(self::$cache['reviews'])) {
            return self::$cache['reviews'];
        }

        $users = self::users()->keyBy('id');

        // product, user, rating, title, body, [quality, transparency, value], verified, helpful, status, days ago, reply
        $rows = [
            [1, 3, 5, 'Une huile exceptionnelle', 'Fruité intense, légère amertume en fin de bouche. Le QR code m\'a permis de voir le moulin et la date de récolte : rassurant.', [5, 5, 4], true, 24, 'published', 3, 'Merci Yasmine ! La récolte 2026 a été particulièrement belle, au plaisir de vous revoir.'],
            [1, 4, 4, 'Très bonne, prix un peu élevé', 'Qualité au rendez-vous et traçabilité complète. Un peu cher mais on sait ce qu\'on paie.', [5, 5, 3], true, 11, 'published', 9, null],
            [1, 7, 5, 'Mon huile de tous les jours', 'Je l\'utilise en cuisine comme en assaisonnement. La bouteille en verre est un plus.', [5, 4, 4], false, 6, 'published', 21, null],
            [2, 5, 5, 'Les meilleures dattes', 'Moelleuses et parfumées. J\'apprécie de savoir que les producteurs sont payés équitablement.', [5, 5, 5], true, 31, 'published', 5, 'Toute la coopérative vous remercie pour ce retour !'],
            [2, 8, 4, 'Bonnes mais barquette abîmée', 'Produit excellent, emballage carton un peu écrasé à la livraison.', [5, 4, 4], true, 3, 'published', 14, null],
            [2, 3, 5, 'Parfait pour le ftour', 'Calibre régulier, aucune datte sèche. La carte du trajet est très parlante.', [5, 5, 4], true, 8, 'published', 40, null],
            [3, 4, 5, 'Harissa authentique', 'Le goût du Cap Bon ! Piquante sans masquer le parfum du piment.', [5, 4, 5], true, 17, 'published', 6, null],
            [3, 10, 3, 'Trop salée à mon goût', 'Bonne harissa mais une teneur en sel élevée.', [3, 4, 4], false, 2, 'published', 26, 'Merci pour votre retour, nous travaillons sur une version moins salée.'],
            [4, 7, 5, 'Juteuses à souhait', 'Des oranges qui ont du goût, et seulement 65 km parcourus. Bravo.', [5, 5, 5], true, 19, 'published', 2, null],
            [4, 5, 4, 'Très bonnes', 'Quelques fruits un peu petits dans la cagette mais excellents.', [4, 4, 5], true, 4, 'published', 11, null],
            [5, 3, 5, 'Un miel de caractère', 'Puissant et aromatique, on sent vraiment le thym. Pot en verre consigné, top.', [5, 5, 4], true, 12, 'published', 16, null],
            [5, 8, 2, 'Cristallisé à la réception', 'Le miel était déjà dur à l\'ouverture. Normal pour un miel brut ?', [3, 4, 2], true, 5, 'published', 9, 'Oui, la cristallisation est naturelle pour un miel non chauffé : un bain-marie doux suffit.'],
            [6, 10, 5, 'Fraîcheur incroyable', 'Cueillies la veille, ça se sent. À manger vite !', [5, 5, 4], true, 7, 'published', 4, null],
            [7, 4, 4, 'Bonnes amandes', 'Croquantes et savoureuses, mais l\'empreinte eau m\'a surpris.', [4, 5, 4], true, 9, 'published', 18, null],
            [7, 3, 4, 'Bon produit', 'Rien à redire sur la qualité.', [4, 4, 4], false, 1, 'pending', 1, null],
            [8, 5, 3, 'Correct', 'Couscous correct mais l\'emballage plastique est dommage.', [4, 3, 4], true, 6, 'published', 22, null],
            [8, 7, 4, 'Bon couscous complet', 'Grain régulier, bonne tenue à la cuisson.', [4, 4, 4], false, 2, 'published', 30, null],
            [9, 8, 4, 'Tendres', 'Cuisson rapide après trempage, très bon goût.', [4, 4, 5], true, 1, 'pending', 2, null],
            [10, 10, 2, 'Pas si « naturel »', 'Étiquette « 100 % naturel » mais la liste mentionne un conservateur. Je trouve ça trompeur.', [3, 1, 2], true, 15, 'flagged', 7, null],
            [10, 4, 4, 'Très parfumées', 'Parfaites en salade ou sur une pizza.', [4, 4, 4], false, 0, 'pending', 1, null],
            [11, 5, 1, 'Packaging « éco » mensonger', 'Présenté comme « éco-responsable » mais barquette plastique non recyclable. Le fromage est bon, la communication non.', [4, 1, 2], true, 28, 'flagged', 5, null],
            [11, 7, 4, 'Bon fromage', 'Belle texture, goût franc.', [4, 4, 3], true, 2, 'published', 13, null],
            [11, 3, 5, 'Délicieux', 'Excellent sur du pain tabouna.', [5, 4, 4], false, 0, 'rejected', 20, null],
            [12, 8, 5, 'Fleur de sel parfaite', 'Croquante, juste ce qu\'il faut. Sachet papier apprécié.', [5, 5, 5], true, 5, 'published', 8, null],
            [12, 10, 4, 'Bon sel', 'Très bon, même si la distance parcourue est importante.', [4, 4, 4], false, 1, 'pending', 3, null],
        ];

        $reviews = collect($rows)->values()->map(function ($r, $i) use ($products, $users) {
            $product = $products[$r[0]];
            $createdAt = Carbon::now()->subDays($r[9])->subHours($i);

            return self::make([
                'id' => $i + 1,
                'product' => $product,
                'user' => $users[$r[1]],
                'rating' => $r[2],
                'title' => $r[3],
                'body' => $r[4],
                'quality_rating' => $r[5][0],
                'transparency_rating' => $r[5][1],
                'value_rating' => $r[5][2],
                'verified_purchase' => $r[6],
                'helpful_count' => $r[7],
                'status' => $r[8],
                'created_at' => $createdAt,
                'reply' => $r[10] ? self::make([
                    'author' => $product->producer->name,
                    'body' => $r[10],
                    'created_at' => $createdAt->copy()->addDay(),
                ]) : null,
                'history' => collect(array_filter([
                    ['label' => 'Avis soumis', 'by' => $users[$r[1]]->name, 'at' => $createdAt, 'note' => null],
                    $r[8] === 'published' ? ['label' => 'Publié automatiquement (aucun mot signalé)', 'by' => 'Système', 'at' => $createdAt->copy()->addMinutes(2), 'note' => null] : null,
                    $r[8] === 'flagged' ? ['label' => 'Signalé par la communauté', 'by' => '3 utilisateurs', 'at' => $createdAt->copy()->addHours(5), 'note' => 'Allégation de greenwashing mentionnée dans l\'avis.'] : null,
                    $r[8] === 'rejected' ? ['label' => 'Rejeté', 'by' => 'Rim Ferchichi', 'at' => $createdAt->copy()->addDay(), 'note' => 'Doublon d\'un avis existant.'] : null,
                ]))->map(fn ($h) => self::make($h))->values(),
            ]);
        });

        foreach ($reviews as $review) {
            $review->user->reviews_count++;
        }

        return self::$cache['reviews'] = $reviews;
    }

    public static function reports(): Collection
    {
        return self::remember('reports', function () {
            $products = self::products()->keyBy('id');
            $actors = self::actors()->keyBy('id');
            $certs = self::certifications()->keyBy('id');
            $users = self::users()->keyBy('id');

            $target = function (string $type, int $id) use ($products, $actors, $certs) {
                return match ($type) {
                    'product' => self::make(['type' => 'product', 'type_label' => 'Produit', 'id' => $id, 'name' => $products[$id]->name, 'subtitle' => $products[$id]->producer->name, 'icon' => $products[$id]->category->icon, 'url' => route('front.products.show', $products[$id]->slug)]),
                    'actor' => self::make(['type' => 'actor', 'type_label' => 'Acteur', 'id' => $id, 'name' => $actors[$id]->name, 'subtitle' => $actors[$id]->city, 'icon' => 'fa-industry', 'url' => route('front.actors.show', $actors[$id]->slug)]),
                    'certification' => self::make(['type' => 'certification', 'type_label' => 'Certification', 'id' => $id, 'name' => $certs[$id]->name, 'subtitle' => $certs[$id]->issuer, 'icon' => 'fa-award', 'url' => route('front.certifications.show', $certs[$id]->slug)]),
                };
            };

            // type, target type, target id, title, status, priority, reporter, assignee, days ago, resolution
            $rows = [
                ['greenwashing', 'product', 11, 'Emballage présenté comme « éco-responsable » alors qu\'il est en plastique', 'confirmed', 'high', 5, 1, 34, 'Allégation retirée de l\'étiquette et de la fiche produit sous 15 jours.'],
                ['misleading_footprint', 'product', 10, 'Empreinte carbone affichée sous-estimée (transport non compté)', 'in_review', 'high', 10, 9, 12, null],
                ['dubious_certification', 'actor', 10, 'Mention « bio » sur les supports sans certificat valide', 'pending', 'critical', 4, null, 2, null],
                ['traceability_error', 'product', 3, 'Étape d\'expédition du lot NT-2026-HAR-0058 non vérifiée', 'in_review', 'medium', 3, 1, 5, null],
                ['health_quality', 'product', 5, 'Pot de miel reçu avec un opercule endommagé', 'resolved', 'low', 8, 9, 40, 'Pot remplacé par le producteur, procédure de conditionnement revue.'],
                ['greenwashing', 'product', 7, 'Slogan « zéro impact » sur des amandes très consommatrices d\'eau', 'confirmed', 'medium', 4, 9, 60, 'Slogan supprimé, empreinte eau désormais affichée sur l\'emballage.'],
                ['dubious_certification', 'certification', 6, 'Le label « raisonnée » est présenté comme équivalent au bio', 'confirmed', 'medium', 7, 1, 75, 'Ajout d\'une mention explicative sur la page du label et des produits concernés.'],
                ['other', 'product', 12, 'Question sur l\'origine exacte des salines', 'rejected', 'low', 10, 9, 28, 'Information déjà disponible : l\'origine est vérifiée sur le lot.'],
                ['traceability_error', 'actor', 6, 'Adresse de l\'atelier différente selon les lots', 'pending', 'medium', 3, null, 1, null],
                ['greenwashing', 'product', 2, 'Visuel « récolte artisanale » sur un lot industriel ?', 'rejected', 'low', 8, 1, 50, 'Vérification sur site : récolte manuelle confirmée par la coopérative.'],
                ['misleading_footprint', 'product', 8, 'Score affiché A en magasin alors que la fiche indique C', 'resolved', 'high', 5, 9, 22, 'Étiquette de rayon corrigée chez le distributeur.'],
                ['health_quality', 'product', 11, 'Date limite de consommation illisible', 'pending', 'high', 7, null, 0, null],
                ['greenwashing', 'actor', 8, 'Affiche « 100 % local » alors qu\'une partie des produits est importée', 'in_review', 'critical', 10, 1, 8, null],
                ['dubious_certification', 'product', 1, 'Numéro de certificat bio introuvable', 'resolved', 'medium', 4, 9, 90, 'Certificat valide retrouvé et lié au produit.'],
                ['other', 'actor', 9, 'Suggestion : afficher les producteurs partenaires', 'rejected', 'low', 3, 1, 15, 'Hors périmètre des signalements, transmis comme suggestion.'],
                ['traceability_error', 'product', 4, 'Date de cueillette postérieure à la date de livraison', 'confirmed', 'high', 3, 9, 45, 'Erreur de saisie corrigée par le producteur, lot revérifié.'],
            ];

            $descriptions = [
                'greenwashing' => 'L\'allégation environnementale mise en avant ne correspond pas aux informations disponibles sur la plateforme. Je joins une photo de l\'emballage et le lien de la fiche.',
                'misleading_footprint' => 'Les chiffres d\'empreinte présentés au consommateur semblent incomplets par rapport aux étapes de transport visibles dans la traçabilité.',
                'dubious_certification' => 'Le label affiché ne figure pas dans la liste des certifications vérifiées de cet acteur. Je n\'ai trouvé aucun numéro de certificat.',
                'traceability_error' => 'Une incohérence apparaît dans la chaîne de traçabilité : les dates ou lieux ne correspondent pas entre deux étapes.',
                'health_quality' => 'Le produit reçu présente un défaut qui pourrait poser un problème de qualité ou de sécurité alimentaire.',
                'other' => 'Remarque générale concernant les informations affichées pour cette cible.',
            ];

            return collect($rows)->values()->map(function ($r, $i) use ($target, $users, $descriptions) {
                $createdAt = Carbon::now()->subDays($r[8])->subHours(3 + $i);
                $reporter = $users[$r[6]];
                $assignee = $r[7] ? $users[$r[7]] : null;
                $closed = in_array($r[4], ['confirmed', 'rejected', 'resolved'], true);

                $history = collect([['label' => 'Signalement soumis', 'by' => $reporter->name, 'at' => $createdAt]]);
                if ($assignee) {
                    $history->push(['label' => 'Pris en charge par '.$assignee->name, 'by' => $assignee->name, 'at' => $createdAt->copy()->addHours(6)]);
                    $history->push(['label' => 'Passé en examen', 'by' => $assignee->name, 'at' => $createdAt->copy()->addHours(7)]);
                }
                if ($closed) {
                    $history->push(['label' => 'Décision : '.StatusBadge::labelFor('report', $r[4]), 'by' => $assignee?->name ?? 'Modération', 'at' => $createdAt->copy()->addDays(min(6, max(1, $r[8] - 1)))]);
                }

                $messages = collect([
                    ['author' => $reporter->name, 'role' => 'reporter', 'body' => 'Bonjour, je vous transmets ce signalement avec les éléments dont je dispose.', 'at' => $createdAt, 'attachments' => ['photo-etiquette.jpg']],
                ]);
                if ($assignee) {
                    $messages->push(['author' => $assignee->name, 'role' => 'moderator', 'body' => 'Merci pour votre signalement. Nous avons contacté l\'acteur concerné et attendons ses justificatifs.', 'at' => $createdAt->copy()->addHours(8), 'attachments' => []]);
                    $messages->push(['author' => $reporter->name, 'role' => 'reporter', 'body' => 'Merci, j\'ajoute une seconde photo prise en magasin.', 'at' => $createdAt->copy()->addHours(20), 'attachments' => ['photo-rayon.jpg']]);
                }

                return self::make([
                    'id' => $i + 1,
                    'ref' => sprintf('SIG-2026-%04d', $i + 1),
                    'type' => $r[0],
                    'target' => $target($r[1], $r[2]),
                    'title' => $r[3],
                    'description' => $descriptions[$r[0]],
                    'evidence' => collect([
                        self::make(['kind' => 'file', 'name' => 'photo-etiquette.jpg', 'size' => '1,2 Mo']),
                        self::make(['kind' => 'link', 'name' => 'Page produit du distributeur', 'url' => 'https://exemple.tn/produit']),
                    ])->take($i % 3 === 0 ? 1 : 2),
                    'status' => $r[4],
                    'priority' => $r[5],
                    'reporter' => $reporter,
                    'assignee' => $assignee,
                    'resolution' => $r[9],
                    'created_at' => $createdAt,
                    'updated_at' => $closed ? $createdAt->copy()->addDays(min(6, max(1, $r[8] - 1))) : $createdAt->copy()->addHours(8),
                    'open_days' => (int) $createdAt->diffInDays(Carbon::now()),
                    'messages' => $messages->map(fn ($m) => self::make($m)),
                    'notes' => $assignee ? collect([
                        self::make(['author' => $assignee->name, 'body' => 'Demande de justificatifs envoyée à l\'acteur (délai 7 jours).', 'at' => $createdAt->copy()->addHours(7)]),
                    ]) : collect(),
                    'history' => $history->map(fn ($h) => self::make($h)),
                ]);
            })->each(fn ($report) => $report->reporter->reports_count++);
        });
    }

    public static function verifications(): Collection
    {
        return self::remember('verifications', function () {
            $actors = self::actors()->keyBy('id');
            $certs = self::certifications()->keyBy('id');

            return collect([
                [1, 10, 1, 'pending', 1, 'certificat-bio-souk-ennour.pdf'],
                [2, 3, 2, 'pending', 3, 'attestation-circuit-court.pdf'],
                [3, 6, 5, 'pending', 4, 'analyse-pesticides-lot-0058.pdf'],
                [4, 2, 3, 'approved', 12, 'contrat-equitable-2026.pdf'],
                [5, 9, 1, 'rejected', 20, 'certificat-expire-2024.pdf'],
                [6, 4, 4, 'pending', 6, 'cahier-des-charges-zaghouan.pdf'],
            ])->map(fn ($v) => self::make([
                'id' => $v[0], 'actor' => $actors[$v[1]], 'certification' => $certs[$v[2]], 'status' => $v[3],
                'submitted_at' => Carbon::now()->subDays($v[4]), 'document' => $v[5],
                'certificate_number' => 'CERT-'.(2026 * 10 + $v[0]).'-'.str_pad((string) ($v[1] * 13), 4, '0', STR_PAD_LEFT),
                'expires_at' => Carbon::now()->addMonths(6 + $v[0] * 2),
                'rejection_reason' => $v[3] === 'rejected' ? 'Certificat expiré depuis 2024.' : null,
            ]));
        });
    }

    public static function emissionFactors(): Collection
    {
        return self::remember('emission_factors', fn () => collect([
            ['Transport routier (camion 19 t)', 'Transport', 'kg CO₂e / t.km', 0.096],
            ['Transport routier réfrigéré', 'Transport', 'kg CO₂e / t.km', 0.132],
            ['Transport maritime (porte-conteneurs)', 'Transport', 'kg CO₂e / t.km', 0.016],
            ['Emballage verre', 'Emballage', 'kg CO₂e / kg', 0.85],
            ['Emballage carton', 'Emballage', 'kg CO₂e / kg', 0.62],
            ['Emballage plastique PET', 'Emballage', 'kg CO₂e / kg', 2.15],
            ['Électricité réseau national', 'Énergie', 'kg CO₂e / kWh', 0.48],
            ['Irrigation goutte-à-goutte', 'Eau', 'L / kg produit', 320],
            ['Engrais azoté de synthèse', 'Intrants', 'kg CO₂e / kg N', 5.6],
            ['Réfrigération (stockage)', 'Énergie', 'kg CO₂e / t.jour', 0.9],
        ])->values()->map(fn ($f, $i) => self::make([
            'id' => $i + 1, 'name' => $f[0], 'category' => $f[1], 'unit' => $f[2], 'value' => $f[3],
            'source' => 'Valeur indicative — exemple pédagogique', 'updated_at' => Carbon::now()->subMonths($i + 1),
        ])));
    }

    /**
     * 12-month series for the charts (oldest month first).
     */
    public static function monthlyStats(): object
    {
        return self::make([
            'labels' => collect(range(11, 0))->map(fn ($m) => ucfirst(Carbon::now()->startOfMonth()->subMonths($m)->translatedFormat('M')))->all(),
            'reviews' => [18, 22, 19, 27, 31, 29, 35, 41, 38, 44, 52, 47],
            'reports' => [4, 6, 5, 7, 9, 6, 8, 11, 9, 12, 10, 8],
            'batches' => [12, 15, 14, 19, 22, 24, 23, 28, 31, 30, 34, 37],
        ]);
    }

    public static function testimonials(): Collection
    {
        return collect([
            ['Yasmine B.', 'Consommatrice, Tunis', 'Je scanne le QR code en magasin et je vois le moulin, la date de récolte et les kilomètres parcourus. Je ne fais plus mes courses autrement.', 5],
            ['Hédi M.', 'Oléiculteur, Sfax', 'NutriTrace valorise notre travail : nos clients voient enfin la différence entre une huile tracée et une huile anonyme.', 5],
            ['Salma J.', 'Consommatrice, Sousse', 'J\'ai signalé une allégation « zéro impact » douteuse. Elle a été retirée en deux semaines. On se sent écoutée.', 5],
            ['Fatma K.', 'Coopérative de dattes, Tozeur', 'Les avis des clients nous aident à améliorer nos emballages, et le label équitable est enfin compris.', 4],
        ])->map(fn ($t) => self::make(['name' => $t[0], 'role' => $t[1], 'quote' => $t[2], 'rating' => $t[3]]));
    }

    public static function faqs(): Collection
    {
        return collect([
            ['Comment retrouver l\'histoire d\'un produit ?', 'Saisissez le numéro de lot imprimé sur l\'emballage (ex. NT-2026-OLV-0412) dans la recherche « Tracer un lot », ou scannez le QR code : chaque étape, de la ferme au rayon, s\'affiche avec ses justificatifs.'],
            ['Qui vérifie les certifications affichées ?', 'Chaque certificat transmis par un acteur est contrôlé par l\'équipe de modération (numéro, organisme, date de validité) avant d\'apparaître avec le badge « Vérifié ».'],
            ['Comment est calculé l\'éco-score ?', 'Il combine cinq indicateurs : émissions de CO₂e par kg, consommation d\'eau, distance parcourue, type d\'emballage et saisonnalité. Le score sur 100 est converti en note de A à E.'],
            ['Que se passe-t-il quand je signale un produit ?', 'Votre signalement reçoit une référence de suivi. Un modérateur l\'examine, contacte l\'acteur concerné et publie une décision. Les cas fondés apparaissent, anonymisés, dans l\'Observatoire.'],
            ['Mes avis sont-ils publiés immédiatement ?', 'Les avis sont publiés après une vérification automatique. Ceux qui contiennent des allégations sensibles passent par une modération humaine.'],
            ['Je suis producteur : comment rejoindre NutriTrace ?', 'Créez un compte, puis complétez votre profil acteur et déposez vos certificats. Notre équipe vous accompagne pour tracer vos premiers lots.'],
        ])->map(fn ($f) => self::make(['question' => $f[0], 'answer' => $f[1]]));
    }

    public static function notifications(): Collection
    {
        return collect([
            ['fa-flag', 'danger', 'Nouveau signalement critique', 'SIG-2026-0003 · Coopérative Souk Ennour', 2],
            ['fa-comment', 'warning', '5 avis en attente de modération', 'Dont 2 signalés par la communauté', 5],
            ['fa-award', 'gold', 'Certificat à vérifier', 'Ferme Ennahl · Produit local', 26],
            ['fa-truck', 'info', 'Lot en transit', 'NT-2026-HAR-0058 · étape non vérifiée', 50],
        ])->map(fn ($n) => self::make(['icon' => $n[0], 'tone' => $n[1], 'title' => $n[2], 'body' => $n[3], 'at' => Carbon::now()->subHours($n[4])]));
    }

    // ------------------------------------------------------------------
    // Finders
    // ------------------------------------------------------------------

    public static function product(string $slug): object
    {
        return self::products()->firstWhere('slug', $slug) ?? abort(404);
    }

    public static function productById(int $id): object
    {
        return self::products()->firstWhere('id', $id) ?? abort(404);
    }

    public static function certification(string $slug): object
    {
        return self::certifications()->firstWhere('slug', $slug) ?? abort(404);
    }

    public static function certificationById(int $id): object
    {
        return self::certifications()->firstWhere('id', $id) ?? abort(404);
    }

    public static function actor(string $slug): object
    {
        return self::actors()->firstWhere('slug', $slug) ?? abort(404);
    }

    public static function actorById(int $id): object
    {
        return self::actors()->firstWhere('id', $id) ?? abort(404);
    }

    public static function batch(string $code): object
    {
        return self::batches()->first(fn ($b) => strcasecmp($b->code, $code) === 0) ?? abort(404);
    }

    public static function batchById(int $id): object
    {
        return self::batches()->firstWhere('id', $id) ?? abort(404);
    }

    public static function review(int $id): object
    {
        return self::reviews()->firstWhere('id', $id) ?? abort(404);
    }

    public static function report(string $ref): object
    {
        return self::reports()->firstWhere('ref', $ref) ?? abort(404);
    }

    public static function user(int $id): object
    {
        return self::users()->firstWhere('id', $id) ?? abort(404);
    }

    public static function productsFor(object $actor): Collection
    {
        return self::products()->filter(fn ($p) => $p->producer->id === $actor->id || $p->processor?->id === $actor->id)->values();
    }

    public static function batchesFor(object $actor): Collection
    {
        return self::batches()->filter(fn ($b) => $b->steps->contains(fn ($s) => $s->actor?->id === $actor->id))->values();
    }

    public static function reviewsFor(object $product): Collection
    {
        return self::reviews()->filter(fn ($r) => $r->product->id === $product->id && $r->status === 'published')->values();
    }

    /**
     * Demo stand-in for "the signed-in consumer": the account space shows the records of demo user #3.
     */
    public static function accountReviews(): Collection
    {
        return self::reviews()->filter(fn ($r) => $r->user->id === 3)->values();
    }

    public static function accountReports(): Collection
    {
        return self::reports()->filter(fn ($r) => $r->reporter->id === 3)->values();
    }

    public static function paginate(Collection $items, int $perPage = 12, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => $pageName, 'query' => request()->query()],
        );
    }

    // ------------------------------------------------------------------

    private static function remember(string $key, callable $build): Collection
    {
        return self::$cache[$key] ??= $build();
    }

    private static function make(array $attributes): object
    {
        return (object) $attributes;
    }
}
