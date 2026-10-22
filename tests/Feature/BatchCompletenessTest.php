<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestion 2 — traceability completeness score of a lot.
 */
class BatchCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_completeness_score_of_the_seeded_lots(): void
    {
        $scores = Batch::with('steps')->get()->mapWithKeys(fn (Batch $batch) => [$batch->code => $batch->completeness]);

        $this->assertSame(100, $scores['NT-2026-OLV-0412']);
        $this->assertSame(77, $scores['NT-2026-HAR-0058']);
        $this->assertSame(73, $scores['NT-2026-MIE-0009']);
    }

    public function test_batch_without_steps_scores_zero(): void
    {
        $batch = Batch::where('code', 'NT-2026-OLV-0412')->firstOrFail();
        $batch->steps()->delete();

        $this->assertSame(0, $batch->fresh('steps')->completeness);
    }

    public function test_score_is_shown_on_the_public_and_admin_lot_pages(): void
    {
        $batch = Batch::where('code', 'NT-2026-OLV-0412')->firstOrFail();

        $this->get(route('front.traceability.batch', $batch->code))
            ->assertOk()->assertSee('Complétude')->assertSee('100 %');

        $admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.batches.show', $batch->id))
            ->assertOk()->assertSee('Complétude');
    }
}