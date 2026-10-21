<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certification;
use App\Models\User;
use App\Services\CatalogAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Gestion 1 — AI (Gemini): product sheet writer and category suggestions on the admin forms.
 */
class CatalogAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
    }

    /** Fakes Gemini answering $answer as its JSON text, and Open Food Facts knowing 42 products for any category. */
    private function fakeGemini(array $answer): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($answer)]]]]]]),
            'search.openfoodfacts.org/*' => Http::response(['facets' => ['nutrition_grades' => ['items' => [['key' => 'c', 'count' => 42]]]]]),
        ]);
    }

    public function test_gemini_drafts_a_product_sheet_and_flags_unproven_claims(): void
    {
        $this->fakeGemini([
            'description' => "  Miel de romarin récolté dans le Djebel Zaghouan,\n doux et floral. Un produit zéro impact en pot de verre. ",
            'composition' => 'Miel de romarin',
        ]);
        $certification = Certification::orderBy('id')->firstOrFail();
        $category = Category::where('slug', 'produits-ruche')->firstOrFail();

        $this->actingAs($this->admin)->postJson('/admin/produits/ia/fiche', [
            'name' => 'Miel de romarin',
            'category_id' => $category->id,
            'region' => 'Zaghouan',
            'certifications' => [$certification->id],
        ])->assertOk()
            ->assertJsonPath('description', 'Miel de romarin récolté dans le Djebel Zaghouan, doux et floral. Un produit zéro impact en pot de verre.')
            ->assertJsonPath('composition', 'Miel de romarin')
            ->assertJsonPath('warnings', ['zéro impact']);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['generationConfig']['responseMimeType'] === 'application/json'
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Produits de la ruche')
            && str_contains($request['contents'][0]['parts'][0]['text'], $certification->name));
    }

    public function test_product_sheet_needs_a_name_a_key_and_an_answer(): void
    {
        $this->actingAs($this->admin)->postJson('/admin/produits/ia/fiche', ['name' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);
        $this->actingAs($this->admin)->postJson('/admin/produits/ia/fiche', ['name' => 'Miel de romarin'])
            ->assertStatus(502)->assertJsonPath('message', CatalogAssistant::UNAVAILABLE);

        config(['services.gemini.key' => null]);
        $this->actingAs($this->admin)->postJson('/admin/produits/ia/fiche', ['name' => 'Miel de romarin'])
            ->assertStatus(503)->assertJsonPath('message', CatalogAssistant::NOT_CONFIGURED);
        Http::assertSentCount(1);
    }

    public function test_gemini_suggests_category_settings_limited_to_known_values(): void
    {
        $categories = Category::pluck('id', 'slug');
        $this->fakeGemini([
            'icon' => 'fa-jar',
            'off_tag' => ' EN:Spreads ',
            'substitutes' => [
                ['id' => $categories['produits-ruche'], 'reason' => 'Même usage au petit-déjeuner.'],
                ['id' => $categories['produits-ruche'], 'reason' => 'Doublon.'],
                ['id' => $categories['fruits'], 'reason' => 'Catégorie modifiée : exclue.'],
                ['id' => 9999, 'reason' => 'Inventée.'],
            ],
            'summary' => 'Une catégorie de pâtes à tartiner.',
        ]);

        $this->actingAs($this->admin)->postJson('/admin/categories/ia/suggestions', ['name' => 'Pâtes à tartiner', 'category_id' => $categories['fruits']])
            ->assertOk()
            ->assertJsonPath('icon', 'fa-jar')
            ->assertJsonPath('off_tag', 'en:spreads')
            ->assertJsonPath('off_products', 42)
            ->assertJsonPath('substitutes', [['id' => $categories['produits-ruche'], 'name' => 'Produits de la ruche', 'reason' => 'Même usage au petit-déjeuner.']]);
    }

    public function test_unknown_icon_and_malformed_tag_are_dropped(): void
    {
        $this->fakeGemini(['icon' => 'fa-rocket', 'off_tag' => 'pâtes à tartiner', 'substitutes' => [], 'summary' => 'Rien de sûr.']);

        $this->actingAs($this->admin)->postJson('/admin/categories/ia/suggestions', ['name' => 'Divers'])
            ->assertOk()
            ->assertJsonPath('icon', null)
            ->assertJsonPath('off_tag', null)
            ->assertJsonPath('off_products', null);
    }

    public function test_ai_endpoints_are_admin_only_and_the_forms_show_the_buttons(): void
    {
        $consumer = User::where('email', 'consumer@nutritrace.tn')->firstOrFail();
        $this->actingAs($consumer)->postJson('/admin/produits/ia/fiche', ['name' => 'Miel'])->assertForbidden();
        $this->actingAs($consumer)->postJson('/admin/categories/ia/suggestions', ['name' => 'Miel'])->assertForbidden();

        $this->actingAs($this->admin)->get('/admin/produits/create')->assertOk()->assertSee('Rédiger avec l’IA');
        $this->actingAs($this->admin)->get('/admin/categories')->assertOk()->assertSee('Suggérer avec l’IA');
    }
}
