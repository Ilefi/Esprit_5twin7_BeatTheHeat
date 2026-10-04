<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Category;
use App\Models\Certification;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $actors = Actor::pluck('id', 'slug');
        $certifications = Certification::pluck('id', 'slug');

        // slug, name, category, producer, processor, region, format, price, certifications, status, description, composition
        $products = [
            ['huile-olive-sfax', 'Huile d\'olive extra vierge de Sfax', 'huiles-condiments', 'domaine-zitouna', 'huilerie-el-baraka', 'Sfax', 'Bouteille verre 75 cl', 32.5, ['bio', 'origine-protegee'], 'published',
                'Huile fruitée vert issue de la variété Chemlali, extraite à froid moins de 24 heures après la récolte.', 'Huile d\'olive vierge extra 100 % Chemlali.'],
            ['dattes-deglet-nour-tozeur', 'Dattes Deglet Nour de Tozeur', 'fruits', 'oasis-nakhla', null, 'Tozeur', 'Barquette carton 500 g', 9.8, ['equitable', 'origine-protegee'], 'published',
                'Dattes branchées translucides au goût de miel, récoltées à la main dans l\'oasis de Tozeur.', 'Dattes Deglet Nour 100 %, sans sirop de glucose ajouté.'],
            ['harissa-cap-bon', 'Harissa traditionnelle du Cap Bon', 'huiles-condiments', 'ferme-ennahl', 'conserverie-cap-bon', 'Nabeul', 'Bocal verre 200 g', 6.4, ['local'], 'published',
                'Piments Baklouti séchés au soleil, broyés à la meule avec ail, carvi et coriandre.', 'Piments 78 %, huile d\'olive, ail, sel, carvi, coriandre.'],
            ['oranges-maltaises-nabeul', 'Oranges maltaises de Nabeul', 'fruits', 'ferme-ennahl', null, 'Nabeul', 'Cagette bois 3 kg', 7.9, ['local', 'agriculture-raisonnee'], 'published',
                'La « blonde » du Cap Bon, juteuse et peu acide, cueillie à maturité en pleine saison.', 'Oranges maltaises demi-sanguines, non traitées après récolte.'],
            ['miel-thym-zaghouan', 'Miel de thym du Djebel Zaghouan', 'produits-ruche', 'bergerie-djebel', null, 'Zaghouan', 'Pot verre 250 g', 24.0, ['local'], 'published',
                'Miel ambré aux notes puissantes, butiné sur le thym sauvage des pentes du djebel.', 'Miel de thym 100 %, non chauffé, non filtré.'],
            ['figues-djebba', 'Figues fraîches de l\'oasis', 'fruits', 'oasis-nakhla', null, 'Tozeur', 'Barquette carton 400 g', 8.5, ['equitable'], 'published',
                'Figues violettes cueillies le matin même, à la chair fondante et sucrée.', 'Figues fraîches 100 %.'],
            ['amandes-sfax', 'Amandes décortiquées de Sfax', 'fruits-secs', 'domaine-zitouna', 'huilerie-el-baraka', 'Sfax', 'Sachet kraft doublé 250 g', 14.9, ['bio'], 'published',
                'Amandes de variété locale, cultivées en sec puis décortiquées mécaniquement.', 'Amandes 100 %.'],
            ['couscous-complet', 'Couscous complet au blé dur', 'epicerie-cereales', 'bergerie-djebel', 'moulin-essafi', 'Béja', 'Sachet plastique 1 kg', 4.6, ['agriculture-raisonnee'], 'published',
                'Couscous roulé à partir de blé dur complet, séché lentement à basse température.', 'Semoule de blé dur complet, eau.'],
            ['pois-chiches-beja', 'Pois chiches du Nord-Ouest', 'epicerie-cereales', 'ferme-ennahl', 'moulin-essafi', 'Béja', 'Sachet plastique 500 g', 3.9, ['local'], 'draft',
                'Pois chiches de petit calibre, tendres à la cuisson, cultivés en rotation avec le blé.', 'Pois chiches secs 100 %.'],
            ['tomates-sechees', 'Tomates séchées au soleil', 'huiles-condiments', 'ferme-ennahl', 'conserverie-cap-bon', 'Nabeul', 'Sachet souple 150 g', 7.2, ['sans-pesticides'], 'pending',
                'Tomates allongées séchées sur claies, puis réhydratées à l\'huile d\'olive.', 'Tomates 92 %, huile d\'olive, sel, origan.'],
            ['fromage-chevre-zaghouan', 'Fromage de chèvre affiné', 'produits-laitiers', 'bergerie-djebel', null, 'Zaghouan', 'Barquette plastique 180 g', 12.0, ['local'], 'published',
                'Petit fromage lactique au lait cru de chèvre, affiné trois semaines.', 'Lait cru de chèvre, sel, ferments lactiques, présure.'],
            ['sel-marin-sfax', 'Fleur de sel des salines de Sfax', 'huiles-condiments', 'domaine-zitouna', null, 'Sfax', 'Sachet papier 250 g', 5.5, ['origine-protegee'], 'published',
                'Cristaux récoltés à la main à la surface des bassins, séchés au soleil et au vent.', 'Fleur de sel marin 100 %, non raffinée.'],
        ];

        foreach ($products as $i => [$slug, $name, $category, $producer, $processor, $region, $format, $price, $certificationSlugs, $status, $description, $composition]) {
            $product = Product::factory()->create([
                'slug' => $slug,
                'name' => $name,
                'category_id' => $categories[$category],
                'producer_id' => $actors[$producer],
                'processor_id' => $processor ? $actors[$processor] : null,
                'region' => $region,
                'format' => $format,
                'price' => $price,
                'status' => $status,
                'description' => $description,
                'composition' => $composition,
                'created_at' => now()->subDays(40 + ($i + 1) * 9),
            ]);

            $product->certifications()->attach($certifications->only($certificationSlugs)->values());
        }
    }
}
