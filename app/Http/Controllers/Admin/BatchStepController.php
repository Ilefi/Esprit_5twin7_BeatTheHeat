<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BatchStepController extends Controller
{
    public function store(Request $request, int $batch): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);
        $data = $request->validateWithBag('step', $this->rules());

        $position = (int) $batch->steps()->max('position') + 1;
        $this->assertChronological($batch, $data['date'], $position, null, 'step');

        $batch->steps()->create($data + [
            'position' => $position,
            'distance_km' => $data['distance_km'] ?? 0,
            'verified' => false,
            'documents' => [],
        ]);

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Étape « {$data['title']} » ajoutée au lot {$batch->code}.");
    }

    public function update(Request $request, int $batch, int $step): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);
        $step = $batch->steps()->findOrFail($step);
        $data = $request->validateWithBag('step'.$step->id, $this->rules());

        $this->assertChronological($batch, $data['date'], $step->position, $step->id, 'step'.$step->id);

        $data['distance_km'] = $data['distance_km'] ?? 0;
        $step->update($data);

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Étape « {$step->title} » mise à jour.");
    }

    public function destroy(int $batch, int $step): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);
        $step = $batch->steps()->findOrFail($step);
        $step->delete();

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Étape « {$step->title} » supprimée.");
    }

    public function move(Request $request, int $batch, int $step): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);
        $step = $batch->steps()->findOrFail($step);
        $request->validate(['direction' => ['required', 'in:up,down']]);
        $up = $request->input('direction') === 'up';

        $steps = $batch->steps()->get()->values();
        $i = $steps->search(fn ($s) => $s->id === $step->id);
        $j = $up ? $i - 1 : $i + 1;

        if (! isset($steps[$j])) {
            return redirect()->route('admin.batches.show', $batch->id);
        }

        // A swap must keep the dates in chronological order.
        $order = $steps->all();
        [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
        for ($k = 1; $k < count($order); $k++) {
            if ($order[$k]->date->lt($order[$k - 1]->date)) {
                return redirect()->route('admin.batches.show', $batch->id)
                    ->with('error', "Déplacement impossible : l'ordre chronologique des dates ne serait plus respecté.");
            }
        }

        DB::transaction(function () use ($steps, $i, $j) {
            $a = $steps[$i];
            $b = $steps[$j];
            [$pa, $pb] = [$a->position, $b->position];
            $temp = $steps->max('position') + 1; // avoids a unique-index clash during the swap
            $a->update(['position' => $temp]);
            $b->update(['position' => $pa]);
            $a->update(['position' => $pb]);
        });

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Étape « {$step->title} » déplacée vers le ".($up ? 'haut' : 'bas').'.');
    }

    /**
     * The step's date must sit between the dates of its neighbours (by position).
     */
    private function assertChronological(Batch $batch, string $date, int $position, ?int $ignoreId, string $bag): void
    {
        $date = Carbon::parse($date);
        $others = $batch->steps()->get()->reject(fn ($s) => $s->id === $ignoreId);
        $prev = $others->filter(fn ($s) => $s->position < $position)->last();
        $next = $others->filter(fn ($s) => $s->position > $position)->first();

        if ($prev && $date->lt($prev->date)) {
            throw ValidationException::withMessages([
                'date' => "La date doit être postérieure ou égale à celle de l'étape précédente ({$prev->date->format('d/m/Y')}).",
            ])->errorBag($bag);
        }
        if ($next && $date->gt($next->date)) {
            throw ValidationException::withMessages([
                'date' => "La date doit être antérieure ou égale à celle de l'étape suivante ({$next->date->format('d/m/Y')}).",
            ])->errorBag($bag);
        }
    }

    private function rules(): array
    {
        return [
            'stage' => ['required', 'in:'.implode(',', array_keys(BatchController::STAGES))],
            'title' => ['required', 'string', 'max:120'],
            'actor_id' => ['nullable', 'integer', 'exists:actors,id'],
            'location' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'action' => ['required', 'string', 'max:500'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:20000'],
        ];
    }
}