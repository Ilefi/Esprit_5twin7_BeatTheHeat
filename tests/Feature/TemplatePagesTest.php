<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke test for every GET page of the UI template (parameterized routes use DemoData slugs).
 */
class TemplatePagesTest extends TestCase
{
    use RefreshDatabase;

    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/a-propos'],
            'how' => ['/comment-ca-marche'],
            'faq' => ['/faq'],
            'contact' => ['/contact'],
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/forgot-password'],
            'products' => ['/produits'],
            'products filtered' => ['/produits?categorie=fruits&eco[]=A&tri=eco&vue=liste'],
            'product' => ['/produits/huile-olive-sfax'],
            'product without batch' => ['/produits/amandes-sfax'],
            'product reviews filtered' => ['/produits/dattes-deglet-nour-tozeur?note=5&verifie=1&tri_avis=useful'],
            'certifications' => ['/certifications'],
            'certification' => ['/certifications/bio'],
            'traceability' => ['/tracabilite'],
            'batch' => ['/tracabilite/lots/NT-2026-OLV-0412'],
            'batch in transit' => ['/tracabilite/lots/NT-2026-HAR-0058'],
            'actors' => ['/acteurs'],
            'actors by type' => ['/acteurs?type=processor'],
            'actor' => ['/acteurs/domaine-zitouna'],
            'impact' => ['/empreinte'],
            'impact compare' => ['/empreinte/comparer'],
            'impact compare three' => ['/empreinte/comparer?produits[]=1&produits[]=4&produits[]=7'],
            'observatory' => ['/observatoire'],
            'observatory filtered' => ['/observatoire?type=greenwashing&issue=confirmed'],
        ];
    }

    public static function protectedPages(): array
    {
        return [
            'report wizard' => ['/signalements/nouveau'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_render_for_guests(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    #[DataProvider('protectedPages')]
    public function test_protected_pages_redirect_guests_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    #[DataProvider('protectedPages')]
    public function test_protected_pages_render_for_consumers(string $uri): void
    {
        $this->actingAs(User::factory()->create())->get($uri)->assertOk();
    }

    public function test_unknown_slugs_return_404(): void
    {
        $this->get('/produits/inconnu')->assertNotFound();
        $this->get('/tracabilite/lots/NT-0000-XXX-0000')->assertNotFound();
    }

    public function test_lot_search_redirects_to_the_lot(): void
    {
        $this->get('/tracabilite?code=nt-2026-olv-0412')->assertRedirect('/tracabilite/lots/NT-2026-OLV-0412');
    }

    public function test_report_wizard_reopens_the_step_with_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/signalements/nouveau')
            ->post('/signalements', ['type' => 'greenwashing', 'target_type' => 'product', 'target_id' => 1])
            ->assertRedirect('/signalements/nouveau')
            ->assertSessionHasErrors(['description', 'consent']);

        $this->actingAs($user)->get('/signalements/nouveau')
            ->assertOk()
            ->assertViewHas('prefill', fn (array $prefill) => $prefill['step'] === 3);
    }

    public function test_review_submission_validates_and_redirects(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/produits/huile-olive-sfax/avis', [
                'rating' => 5, 'quality_rating' => 5, 'transparency_rating' => 4, 'value_rating' => 4,
                'title' => 'Excellent', 'body' => 'Une huile vraiment remarquable, très fruitée.',
            ])
            ->assertRedirect('/produits/huile-olive-sfax#avis')
            ->assertSessionHas('success');
    }
}
