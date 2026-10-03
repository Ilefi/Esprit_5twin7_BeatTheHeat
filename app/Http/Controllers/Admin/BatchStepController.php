<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BatchStepController extends Controller
{
    public function store(Request $request, int $batch): RedirectResponse
    {
        $batch = DemoData::batchById($batch);
        $data = $request->validateWithBag('step', $this->rules());

        // TODO(Gestion 2): $batch->steps()->create($data)
        return redirect()->route('admin.batches.show', $batch->id)->with('success', "Étape « {$data['title']} » ajoutée au lot {$batch->code}.");
    }

    public function update(Request $request, int $batch, int $step): RedirectResponse
    {
        $batch = DemoData::batchById($batch);
        $data = $request->validateWithBag('step'.$step, $this->rules());

        return redirect()->route('admin.batches.show', $batch->id)->with('success', "Étape « {$data['title']} » mise à jour.");
    }

    public function destroy(int $batch, int $step): RedirectResponse
    {
        $batch = DemoData::batchById($batch);
        $step = $batch->steps->firstWhere('id', $step) ?? abort(404);

        return redirect()->route('admin.batches.show', $batch->id)->with('success', "Étape « {$step->title} » supprimée.");
    }

    public function move(Request $request, int $batch, int $step): RedirectResponse
    {
        $batch = DemoData::batchById($batch);
        $step = $batch->steps->firstWhere('id', $step) ?? abort(404);
        $request->validate(['direction' => ['required', 'in:up,down']]);

        // TODO(Gestion 2): swap positions with the neighbouring step
        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Étape « {$step->title} » déplacée vers le ".($request->input('direction') === 'up' ? 'haut' : 'bas').'.');
    }

    private function rules(): array
    {
        return [
            'stage' => ['required', 'in:'.implode(',', array_keys(BatchController::STAGES))],
            'title' => ['required', 'string', 'max:120'],
            'actor_id' => ['nullable', 'integer'],
            'location' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'action' => ['required', 'string', 'max:500'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:20000'],
        ];
    }
}
