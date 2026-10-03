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
            'report wizard prefilled' => ['/signalements/nouveau?type=greenwashing&cible=actor&id=3'],
            'account dashboard' => ['/mon-espace'],
            'account reviews' => ['/mon-espace/avis'],
            'account reviews filtered' => ['/mon-espace/avis?statut=published'],
            'account reports' => ['/mon-espace/signalements'],
            'account reports filtered' => ['/mon-espace/signalements?statut=in_review'],
            'account report open' => ['/mon-espace/signalements/SIG-2026-0004'],
            'account report closed' => ['/mon-espace/signalements/SIG-2026-0016'],
            'profile' => ['/profile'],
        ];
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['/admin'],
            'products' => ['/admin/produits'],
            'products filtered' => ['/admin/produits?q=huile&statut=published&eco=B&categorie=1'],
            'product create' => ['/admin/produits/create'],
            'product show' => ['/admin/produits/1'],
            'product edit' => ['/admin/produits/1/edit'],
            'categories' => ['/admin/categories'],
            'certifications' => ['/admin/certifications'],
            'certification create' => ['/admin/certifications/create'],
            'certification edit' => ['/admin/certifications/1/edit'],
            'verifications' => ['/admin/certifications/verifications'],
            'verifications rejected' => ['/admin/certifications/verifications?statut=rejected'],
            'actors' => ['/admin/acteurs'],
            'actor create' => ['/admin/acteurs/create'],
            'actor edit' => ['/admin/acteurs/1/edit'],
            'batches' => ['/admin/lots'],
            'batch create' => ['/admin/lots/create'],
            'batch show' => ['/admin/lots/1'],
            'batch edit' => ['/admin/lots/1/edit'],
            'impacts' => ['/admin/empreinte'],
            'impacts filtered' => ['/admin/empreinte?eco=A'],
            'impact create' => ['/admin/empreinte/create'],
            'impact edit' => ['/admin/empreinte/1/edit'],
            'emission factors' => ['/admin/empreinte/facteurs'],
            'reviews' => ['/admin/avis'],
            'reviews flagged' => ['/admin/avis?statut=flagged&note=1'],
            'review show' => ['/admin/avis/21'],
            'reports' => ['/admin/signalements'],
            'reports filtered' => ['/admin/signalements?type=greenwashing&priorite=high&periode=90'],
            'reports kanban' => ['/admin/signalements?vue=kanban'],
            'report show' => ['/admin/signalements/SIG-2026-0001'],
            'users' => ['/admin/utilisateurs'],
            'users by role' => ['/admin/utilisateurs?role=admin'],
            'user edit' => ['/admin/utilisateurs/3/modifier'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_render_for_admins(string $uri): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get($uri)->assertOk();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_are_forbidden_for_consumers(string $uri): void
    {
        $this->actingAs(User::factory()->create())->get($uri)->assertForbidden();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_redirect_guests_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    public function test_dashboard_route_redirects_by_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/dashboard')->assertRedirect('/admin');
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertRedirect('/mon-espace');
    }

    public function test_admin_forms_validate_and_redirect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/produits/create')->post('/admin/produits', [])
            ->assertRedirect('/admin/produits/create')
            ->assertSessionHasErrors(['name', 'category_id', 'price']);

        $this->actingAs($admin)->patch('/admin/signalements/SIG-2026-0002', ['status' => 'resolved', 'priority' => 'high'])
            ->assertSessionHasErrors('resolution');

        $this->actingAs($admin)->from('/admin/avis')->post('/admin/avis/lot', ['ids' => [1, 2], 'action' => 'published'])
            ->assertRedirect('/admin/avis')
            ->assertSessionHas('success', '2 avis approuvés.');
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
