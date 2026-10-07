<?php

namespace Tests\Feature;

use App\Models\Impact;
use App\Models\Product;
use App\Models\User;
use App\Support\EcoScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestion 3 — Empreinte environnementale: admin CRUD, computed eco-score and the product page.
 */
class ImpactTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_creates_a_footprint_and_the_eco_score_is_computed(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->post('/admin/empreinte', $this->impactData(['product_id' => $product->id]))
            ->assertRedirect('/admin/empreinte')
            ->assertSessionHas('success');

        $impact = $product->impact()->firstOrFail();
        $expected = EcoScore::points(0.5, 300, 50, 'compostable', true);
        $this->assertSame($expected, $impact->eco_points);
        $this->assertSame(EcoScore::grade($expected), $impact->eco_score);
        $this->assertSame(['Production' => 70, 'Transformation' => 0, 'Transport' => 20, 'Emballage' => 10], $impact->breakdown);
        $this->assertSame('lca', $impact->methodology);
    }

    public function test_footprint_validation_checks_ranges_and_consistency(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->from('/admin/empreinte/create')->post('/admin/empreinte', $this->impactData([
            'product_id' => $product->id,
            'co2_per_kg' => -1,
            'water_per_kg' => 'beaucoup',
            'distance_km' => 25000,
            'methodology' => 'magic',
        ]))
            ->assertRedirect('/admin/empreinte/create')
            ->assertSessionHasErrors(['co2_per_kg', 'water_per_kg', 'distance_km', 'methodology']);

        // Shares that do not add up to 100 %.
        $this->actingAs($this->admin)->post('/admin/empreinte', $this->impactData([
            'product_id' => $product->id,
            'breakdown' => ['Production' => 50, 'Transformation' => 10, 'Transport' => 10, 'Emballage' => 10],
        ]))->assertSessionHasErrors('breakdown');

        // A product already having a footprint cannot get a second one.
        $this->actingAs($this->admin)->post('/admin/empreinte', $this->impactData([
            'product_id' => Product::where('slug', 'huile-olive-sfax')->value('id'),
        ]))->assertSessionHasErrors('product_id');

        $this->assertNull($product->impact()->first());
    }

    public function test_admin_updates_a_footprint_and_the_grade_follows(): void
    {
        $product = Product::where('slug', 'fromage-chevre-zaghouan')->firstOrFail();
        $this->assertSame('E', $product->impact->eco_score);

        $this->actingAs($this->admin)->put("/admin/empreinte/{$product->id}", $this->impactData())
            ->assertRedirect('/admin/empreinte');

        $this->assertSame('A', $product->impact()->first()->eco_score);
    }

    public function test_admin_deletes_a_footprint_and_the_product_page_still_renders(): void
    {
        $product = Product::where('slug', 'huile-olive-sfax')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/empreinte/{$product->id}")->assertRedirect('/admin/empreinte');
        $this->assertModelExists($product);
        $this->assertSame(0, Impact::where('product_id', $product->id)->count());

        $this->get('/produits/huile-olive-sfax')->assertOk()->assertSee('Éco-score non évalué')->assertSee('Empreinte en cours d\'évaluation');
        $this->actingAs($this->admin)->get("/admin/produits/{$product->id}")->assertOk()->assertSee('Saisir l\'empreinte', false);
        $this->actingAs($this->admin)->get("/admin/empreinte/{$product->id}/edit")->assertNotFound();
    }

    public function test_product_page_shows_the_environmental_score_and_methodology(): void
    {
        $this->get('/produits/huile-olive-sfax')
            ->assertOk()
            ->assertSee('Éco-score')
            ->assertSee(EcoScore::METHODOLOGIES['lca'])
            ->assertSee('ACV simplifiée de la coopérative');
    }

    private function impactData(array $overrides = []): array
    {
        return $overrides + [
            'co2_per_kg' => 0.5,
            'water_per_kg' => 300,
            'distance_km' => 50,
            'packaging' => 'compostable',
            'seasonal' => 1,
            'methodology' => 'lca',
            'source' => 'Étude de test',
            'breakdown' => ['Production' => 70, 'Transformation' => 0, 'Transport' => 20, 'Emballage' => 10],
        ];
    }
}
