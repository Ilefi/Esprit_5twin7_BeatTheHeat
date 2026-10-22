<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestion 2 — verify / unverify a traceability step (and its effect on the completeness score).
 */
class BatchStepVerifyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@nutritrace.tn')->firstOrFail();
    }

    public function test_verifying_the_last_step_raises_completeness_from_77_to_87(): void
    {
        $batch = Batch::where('code', 'NT-2026-HAR-0058')->firstOrFail();
        $this->assertSame(77, $batch->completeness);

        $step = $batch->steps()->where('position', 4)->firstOrFail();
        $this->assertFalse($step->verified);

        $this->actingAs($this->admin)
            ->post(route('admin.batches.steps.verify', [$batch->id, $step->id]))
            ->assertRedirect(route('admin.batches.show', $batch->id))
            ->assertSessionHas('success');

        $this->assertTrue($step->fresh()->verified);
        $this->assertSame(87, $batch->fresh('steps')->completeness);

        // Clicking again un-verifies it and drops the score back.
        $this->actingAs($this->admin)
            ->post(route('admin.batches.steps.verify', [$batch->id, $step->id]))
            ->assertRedirect(route('admin.batches.show', $batch->id))
            ->assertSessionHas('success');

        $this->assertFalse($step->fresh()->verified);
        $this->assertSame(77, $batch->fresh('steps')->completeness);
    }

    public function test_a_step_without_an_actor_cannot_be_verified(): void
    {
        $step = BatchStep::whereNull('actor_id')->where('stage', 'consumer')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.batches.steps.verify', [$step->batch_id, $step->id]))
            ->assertRedirect(route('admin.batches.show', $step->batch_id))
            ->assertSessionHas('error');

        $this->assertFalse($step->fresh()->verified);
    }

    public function test_verify_is_admin_only(): void
    {
        $consumer = User::where('email', 'consumer@nutritrace.tn')->firstOrFail();
        $batch = Batch::where('code', 'NT-2026-HAR-0058')->firstOrFail();
        $step = $batch->steps()->where('position', 4)->firstOrFail();

        $this->actingAs($consumer)
            ->post(route('admin.batches.steps.verify', [$batch->id, $step->id]))
            ->assertForbidden();

        $this->assertFalse($step->fresh()->verified);
    }
}
