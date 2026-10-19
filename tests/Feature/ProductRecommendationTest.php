<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductRecommender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Gestion 1 — innovation: "Meilleures alternatives", category benchmark and the Open Food Facts integration.
 * Seeded eco points: oranges 99, figues 94, miel 81, dattes 79, harissa 76, huile 75, sel 70, amandes 48.
 */
class ProductRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug): Product
    {
        return Product::where('slug', $slug)->firstOrFail();
    }

    private function fakeOpenFoodFacts(): void
    {
        Http::fake([
            'world.openfoodfacts.org/api/v2/product/3017620422003*' => Http::response(['status' => 1, 'product' => [
                'product_name_fr' => 'Miel de fleurs',
                'generic_name_fr' => 'Miel de fleurs toutes origines, crémeux',
                'brands' => 'Rucher test',
                'quantity' => '500 g',
                'ingredients_text_fr' => 'Miel 100 %.',
                'nutriscore_grade' => 'd',
                'nova_group' => 1,
                'ecoscore_grade' => 'a-plus',
                'allergens_tags' => ['en:milk', 'en:unknown-thing'],
                'categories_tags' => ['en:spreads', 'en:honeys'],
                'nutriments' => ['energy-kcal_100g' => 330, 'sugars_100g' => 82.4, 'salt_100g' => 'n/a'],
            ]]),
            'world.openfoodfacts.org/api/v2/product/*' => Http::response(['status' => 0, 'status_verbose' => 'product not found']),
            'search.openfoodfacts.org/*' => Http::response(['facets' => [
                'nutrition_grades' => ['items' => [['key' => 'd', 'count' => 6], ['key' => 'e', 'count' => 2], ['key' => 'unknown', 'count' => 12]]],
                'ecoscore_grade' => ['items' => [['key' => 'a-plus', 'count' => 1], ['key' => 'b', 'count' => 3], ['key' => 'c', 'count' => 4]]],
            ]]),
        ]);
    }

    public function test_alternatives_are_better_and_come_from_the_category_and_its_substitutes(): void
    {
        $alternatives = app(ProductRecommender::class)->alternatives($this->product('amandes-sfax'));

        // Fruits secs ↔ Fruits and Fruits secs ↔ Produits de la ruche are seeded substitutes.
        $this->assertCount(3, $alternatives);
        $this->assertSame('oranges-maltaises-nabeul', $alternatives->first()->slug);
        $this->assertTrue($alternatives->every(fn (Product $p) => $p->impact->eco_points > 48));
        $this->assertTrue($alternatives->every(fn (Product $p) => in_array($p->category->slug, ['fruits', 'produits-ruche'], true)));
        $this->assertContains('Éco-score A au lieu de D', $alternatives->first()->recommendation['reasons']);
    }

    public function test_without_substitutes_alternatives_stay_in_the_category(): void
    {
        $alternatives = app(ProductRecommender::class)->alternatives($this->product('sel-marin-sfax'));

        $this->assertEqualsCanonicalizing(['harissa-cap-bon', 'huile-olive-sfax'], $alternatives->pluck('slug')->all());
    }

    public function test_the_best_product_has_no_alternative_and_tops_its_category(): void
    {
        $oranges = $this->product('oranges-maltaises-nabeul');

        $this->assertTrue(app(ProductRecommender::class)->alternatives($oranges)->isEmpty());
        $this->assertSame(['rank' => 1, 'count' => 3, 'better_than' => 100, 'average_points' => 91, 'average_grade' => 'A'],
            app(ProductRecommender::class)->categoryBenchmark($oranges));

        $this->get('/produits/oranges-maltaises-nabeul')->assertOk()
            ->assertDontSee('Meilleures alternatives')
            ->assertSee('Plus durable que <strong>100 %</strong>', false);
    }

    public function test_product_page_shows_alternatives_and_open_food_facts_data(): void
    {
        $this->fakeOpenFoodFacts();
        $this->product('miel-thym-zaghouan')->update(['barcode' => '3017620422003']);

        $this->get('/produits/amandes-sfax')->assertOk()
            ->assertSee('Meilleures alternatives')
            ->assertSee('Oranges maltaises de Nabeul');

        $this->get('/produits/miel-thym-zaghouan')->assertOk()
            ->assertSee('Nutri-Score D')
            ->assertSee('Aliment pas ou peu transformé')
            ->assertSee('Lait')
            ->assertSee('330,0 kcal')
            ->assertDontSee('n/a')
            // 20 OFF products in en:honeys; Green-Score mean of A, B, B, B, C, C, C, C = 2.4 → B, and the miel is A.
            ->assertSee('20 produits référencés')
            ->assertSee('mieux que la moyenne mondiale de sa catégorie (B)');

        Http::assertSent(fn ($request) => $request->hasHeader('User-Agent', config('services.openfoodfacts.user_agent')));
    }

    public function test_product_page_still_renders_when_open_food_facts_is_down(): void
    {
        Http::fake(['*' => Http::response('Service unavailable', 503)]);
        $this->product('miel-thym-zaghouan')->update(['barcode' => '3017620422003']);

        $this->get('/produits/miel-thym-zaghouan')->assertOk()
            ->assertSee('Pas de fiche nutritionnelle')
            ->assertDontSee('produits référencés');
    }

    public function test_admin_imports_a_product_from_its_barcode(): void
    {
        $this->fakeOpenFoodFacts();
        $admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();

        $this->actingAs($admin)->getJson('/admin/produits/open-food-facts/3017620422003')->assertOk()
            ->assertJsonPath('fields.name', 'Miel de fleurs')
            ->assertJsonPath('fields.format', '500 g')
            ->assertJsonPath('fields.composition', 'Miel 100 %.')
            // en:honeys is the off_tag of "Produits de la ruche".
            ->assertJsonPath('fields.category_id', Category::where('slug', 'produits-ruche')->value('id'))
            ->assertJsonPath('summary.allergens', ['Lait']);

        $this->actingAs($admin)->getJson('/admin/produits/open-food-facts/12345678')->assertNotFound()
            ->assertJsonPath('message', 'Aucun produit trouvé sur Open Food Facts pour le code-barres 12345678.');

        $consumer = User::where('email', 'consumer@nutritrace.tn')->firstOrFail();
        $this->actingAs($consumer)->getJson('/admin/produits/open-food-facts/3017620422003')->assertForbidden();
    }

    public function test_product_barcode_is_validated(): void
    {
        $admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
        $miel = $this->product('miel-thym-zaghouan');
        $miel->update(['barcode' => '3017620422003']);

        $this->actingAs($admin)->post('/admin/produits', ['barcode' => '12AB'])->assertSessionHasErrors('barcode');
        $this->actingAs($admin)->post('/admin/produits', ['barcode' => '3017620422003'])
            ->assertSessionHasErrors(['barcode' => 'Un produit utilise déjà ce code-barres.']);
    }

    public function test_admin_sets_the_open_food_facts_tag_and_symmetric_substitutes(): void
    {
        $admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
        $categories = Category::pluck('id', 'slug');
        $oils = Category::findOrFail($categories['huiles-condiments']);

        $this->actingAs($admin)->put("/admin/categories/{$oils->id}", [
            'name' => $oils->name,
            'icon' => $oils->icon,
            'off_tag' => 'en:vegetable-oils',
            'substitutes' => [$categories['epicerie-cereales']],
        ])->assertRedirect(route('admin.categories.index'))->assertSessionHasNoErrors();

        $this->assertSame('en:vegetable-oils', $oils->fresh()->off_tag);
        $this->assertSame([$categories['epicerie-cereales']], $oils->substitutes()->pluck('id')->all());
        $this->assertSame([$oils->id], Category::findOrFail($categories['epicerie-cereales'])->substitutes()->pluck('id')->all());

        // Removing the link also removes the mirrored one.
        $this->actingAs($admin)->put("/admin/categories/{$oils->id}", ['name' => $oils->name, 'icon' => $oils->icon]);
        $this->assertTrue(Category::findOrFail($categories['epicerie-cereales'])->substitutes->isEmpty());

        $this->actingAs($admin)->put("/admin/categories/{$oils->id}", [
            'name' => $oils->name,
            'icon' => $oils->icon,
            'off_tag' => 'Huiles',
            'substitutes' => [$oils->id],
        ])->assertSessionHasErrorsIn("editCategory{$oils->id}", ['off_tag', 'substitutes.0']);

        $this->actingAs($admin)->get('/admin/categories')->assertOk()->assertSee('Éco-score moyen')->assertSee('en:honeys');
    }
}
