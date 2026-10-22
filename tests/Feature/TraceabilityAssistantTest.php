<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\User;
use App\Services\CatalogAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Gestion 2 — AI (Gemini): draft of the "action réalisée" text on the step form of a lot.
 */
class TraceabilityAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
        $this->batch = Batch::where('code', 'NT-2026-OLV-0412')->firstOrFail();
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
    }

    private function url(?int $batchId = null): string
    {
        return route('admin.batches.steps.ai', $batchId ?? $this->batch->id);
    }

    private function fakeGemini(array $answer): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($answer)]]]]]]),
        ]);
    }

    public function test_gemini_drafts_a_step_action_and_flags_unproven_claims_and_invented_figures(): void
    {
        $this->fakeGemini(['action' => "  Récolte manuelle de 18 t d'olives,\n produit zéro impact. "]);

        $this->actingAs($this->admin)->postJson($this->url(), [
            'stage' => 'production',
            'title' => 'Récolte des olives',
            'location' => 'Oliveraie Zitouna, Sfax',
        ])->assertOk()
            ->assertJsonPath('action', "Récolte manuelle de 18 t d'olives, produit zéro impact.")
            ->assertJsonPath('warnings', ['Allégation à vérifier : zéro impact', 'Chiffre non fourni : 18']);

        Http::assertSent(fn (Request $request) => $request->hasHeader('x-goog-api-key', 'test-key')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Récolte des olives')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Oliveraie Zitouna'));
    }

    public function test_step_draft_needs_a_title_a_key_and_an_answer(): void
    {
        $payload = ['stage' => 'production', 'title' => 'Récolte des olives'];

        $this->actingAs($this->admin)->postJson($this->url(), ['stage' => 'production', 'title' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('title');
        $this->actingAs($this->admin)->postJson($this->url(), ['stage' => 'unknown', 'title' => 'Récolte'])
            ->assertUnprocessable()->assertJsonValidationErrors('stage');

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);
        $this->actingAs($this->admin)->postJson($this->url(), $payload)
            ->assertStatus(502)->assertJsonPath('message', CatalogAssistant::UNAVAILABLE);

        config(['services.gemini.key' => null]);
        $this->actingAs($this->admin)->postJson($this->url(), $payload)
            ->assertStatus(503)->assertJsonPath('message', CatalogAssistant::NOT_CONFIGURED);
        Http::assertSentCount(1);
    }

    public function test_step_draft_is_admin_only_and_needs_an_existing_lot(): void
    {
        $consumer = User::where('email', 'consumer@nutritrace.tn')->firstOrFail();
        $payload = ['stage' => 'production', 'title' => 'Récolte des olives'];

        $this->actingAs($consumer)->postJson($this->url(), $payload)->assertForbidden();
        $this->actingAs($this->admin)->postJson($this->url(999999), $payload)->assertNotFound();
    }
}
