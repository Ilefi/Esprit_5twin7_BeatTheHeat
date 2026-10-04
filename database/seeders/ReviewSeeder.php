<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::pluck('id', 'slug');
        $users = User::pluck('id', 'email');

        // product, author, rating, title, body, [quality, transparency, value], verified, helpful, status, days ago, producer reply
        $reviews = [
            ['huile-olive-sfax', 'consumer@nutritrace.tn', 5, 'Une huile exceptionnelle', 'Fruité intense, légère amertume en fin de bouche. Le QR code m\'a permis de voir le moulin et la date de récolte : rassurant.', [5, 5, 4], true, 24, 'published', 3, 'Merci Yasmine ! La récolte 2026 a été particulièrement belle, au plaisir de vous revoir.'],
            ['huile-olive-sfax', 'mehdi.g@example.tn', 4, 'Très bonne, prix un peu élevé', 'Qualité au rendez-vous et traçabilité complète. Un peu cher mais on sait ce qu\'on paie.', [5, 5, 3], true, 11, 'published', 9, null],
            ['huile-olive-sfax', 'ines.m@example.tn', 5, 'Mon huile de tous les jours', 'Je l\'utilise en cuisine comme en assaisonnement. La bouteille en verre est un plus.', [5, 4, 4], false, 6, 'published', 21, null],
            ['dattes-deglet-nour-tozeur', 'salma.j@example.tn', 5, 'Les meilleures dattes', 'Moelleuses et parfumées. J\'apprécie de savoir que les producteurs sont payés équitablement.', [5, 5, 5], true, 31, 'published', 5, 'Toute la coopérative vous remercie pour ce retour !'],
            ['dattes-deglet-nour-tozeur', 'walid.c@example.tn', 4, 'Bonnes mais barquette abîmée', 'Produit excellent, emballage carton un peu écrasé à la livraison.', [5, 4, 4], true, 3, 'published', 14, null],
            ['dattes-deglet-nour-tozeur', 'consumer@nutritrace.tn', 5, 'Parfait pour le ftour', 'Calibre régulier, aucune datte sèche. La carte du trajet est très parlante.', [5, 5, 4], true, 8, 'published', 40, null],
            ['harissa-cap-bon', 'mehdi.g@example.tn', 5, 'Harissa authentique', 'Le goût du Cap Bon ! Piquante sans masquer le parfum du piment.', [5, 4, 5], true, 17, 'published', 6, null],
            ['harissa-cap-bon', 'oussama.b@example.tn', 3, 'Trop salée à mon goût', 'Bonne harissa mais une teneur en sel élevée.', [3, 4, 4], false, 2, 'published', 26, 'Merci pour votre retour, nous travaillons sur une version moins salée.'],
            ['oranges-maltaises-nabeul', 'ines.m@example.tn', 5, 'Juteuses à souhait', 'Des oranges qui ont du goût, et seulement 65 km parcourus. Bravo.', [5, 5, 5], true, 19, 'published', 2, null],
            ['oranges-maltaises-nabeul', 'salma.j@example.tn', 4, 'Très bonnes', 'Quelques fruits un peu petits dans la cagette mais excellents.', [4, 4, 5], true, 4, 'published', 11, null],
            ['miel-thym-zaghouan', 'consumer@nutritrace.tn', 5, 'Un miel de caractère', 'Puissant et aromatique, on sent vraiment le thym. Pot en verre consigné, top.', [5, 5, 4], true, 12, 'published', 16, null],
            ['miel-thym-zaghouan', 'walid.c@example.tn', 2, 'Cristallisé à la réception', 'Le miel était déjà dur à l\'ouverture. Normal pour un miel brut ?', [3, 4, 2], true, 5, 'published', 9, 'Oui, la cristallisation est naturelle pour un miel non chauffé : un bain-marie doux suffit.'],
            ['figues-djebba', 'oussama.b@example.tn', 5, 'Fraîcheur incroyable', 'Cueillies la veille, ça se sent. À manger vite !', [5, 5, 4], true, 7, 'published', 4, null],
            ['amandes-sfax', 'mehdi.g@example.tn', 4, 'Bonnes amandes', 'Croquantes et savoureuses, mais l\'empreinte eau m\'a surpris.', [4, 5, 4], true, 9, 'published', 18, null],
            ['amandes-sfax', 'consumer@nutritrace.tn', 4, 'Bon produit', 'Rien à redire sur la qualité.', [4, 4, 4], false, 1, 'pending', 1, null],
            ['couscous-complet', 'salma.j@example.tn', 3, 'Correct', 'Couscous correct mais l\'emballage plastique est dommage.', [4, 3, 4], true, 6, 'published', 22, null],
            ['couscous-complet', 'ines.m@example.tn', 4, 'Bon couscous complet', 'Grain régulier, bonne tenue à la cuisson.', [4, 4, 4], false, 2, 'published', 30, null],
            ['pois-chiches-beja', 'walid.c@example.tn', 4, 'Tendres', 'Cuisson rapide après trempage, très bon goût.', [4, 4, 5], true, 1, 'pending', 2, null],
            ['tomates-sechees', 'oussama.b@example.tn', 2, 'Pas si « naturel »', 'Étiquette « 100 % naturel » mais la liste mentionne un conservateur. Je trouve ça trompeur.', [3, 1, 2], true, 15, 'flagged', 7, null],
            ['tomates-sechees', 'mehdi.g@example.tn', 4, 'Très parfumées', 'Parfaites en salade ou sur une pizza.', [4, 4, 4], false, 0, 'pending', 1, null],
            ['fromage-chevre-zaghouan', 'salma.j@example.tn', 1, 'Packaging « éco » mensonger', 'Présenté comme « éco-responsable » mais barquette plastique non recyclable. Le fromage est bon, la communication non.', [4, 1, 2], true, 28, 'flagged', 5, null],
            ['fromage-chevre-zaghouan', 'ines.m@example.tn', 4, 'Bon fromage', 'Belle texture, goût franc.', [4, 4, 3], true, 2, 'published', 13, null],
            ['fromage-chevre-zaghouan', 'consumer@nutritrace.tn', 5, 'Délicieux', 'Excellent sur du pain tabouna.', [5, 4, 4], false, 0, 'rejected', 20, null],
            ['sel-marin-sfax', 'walid.c@example.tn', 5, 'Fleur de sel parfaite', 'Croquante, juste ce qu\'il faut. Sachet papier apprécié.', [5, 5, 5], true, 5, 'published', 8, null],
            ['sel-marin-sfax', 'oussama.b@example.tn', 4, 'Bon sel', 'Très bon, même si la distance parcourue est importante.', [4, 4, 4], false, 1, 'pending', 3, null],
        ];

        foreach ($reviews as $i => [$product, $author, $rating, $title, $body, $subRatings, $verified, $helpful, $status, $daysAgo, $reply]) {
            $createdAt = now()->subDays($daysAgo)->subHours($i);

            $review = Review::factory()->create([
                'product_id' => $products[$product],
                'user_id' => $users[$author],
                'rating' => $rating,
                'quality_rating' => $subRatings[0],
                'transparency_rating' => $subRatings[1],
                'value_rating' => $subRatings[2],
                'title' => $title,
                'body' => $body,
                'verified_purchase' => $verified,
                'helpful_count' => $helpful,
                'status' => $status,
                'reply_body' => $reply,
                'replied_at' => $reply ? $createdAt->copy()->addDay() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->recordHistory($review);
        }

        // Older reviews written by the generated consumers, spread over the last 12 months (feeds the dashboard charts).
        $consumers = User::where('role', 'consumer')->where('email', 'not like', '%.tn')->get();

        Review::factory(80)
            ->recycle($consumers)
            ->recycle(Product::published()->get())
            ->create()
            ->each(fn (Review $review) => $this->recordHistory($review));
    }

    private function recordHistory(Review $review): void
    {
        $at = $review->created_at;

        $review->history()->create(['label' => 'Avis soumis', 'author' => $review->user->name, 'created_at' => $at]);

        match ($review->status) {
            'published' => $review->history()->create(['label' => 'Publié automatiquement (aucun mot signalé)', 'author' => 'Système', 'created_at' => $at->copy()->addMinutes(2)]),
            'flagged' => $review->history()->create(['label' => 'Signalé par la communauté', 'author' => '3 utilisateurs', 'note' => 'Allégation de greenwashing mentionnée dans l\'avis.', 'created_at' => $at->copy()->addHours(5)]),
            'rejected' => $review->history()->create(['label' => 'Rejeté', 'author' => 'Rim Ferchichi', 'note' => 'Doublon d\'un avis existant.', 'created_at' => $at->copy()->addDay()]),
            default => null,
        };
    }
}
