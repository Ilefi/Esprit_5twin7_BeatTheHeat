<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public const STAGES = [
        'production' => 'Production',
        'processing' => 'Transformation',
        'distribution' => 'Distribution',
        'consumer' => 'Consommateur',
    ];

    public function index(Request $request): View
    {
        // TODO(Gestion 2): replace DemoData with Batch::with('product')->withCount('steps')->filter($request)->paginate()
        $batches = DemoData::batches();

        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('batch'))) {
            $batches = $batches->where('status', $status);
        }
        if ($search = trim((string) $request->query('q'))) {
            $batches = $batches->filter(fn ($b) => str_contains(mb_strtolower($b->code.' '.$b->product->name), mb_strtolower($search)));
        }

        return view('admin.batches.index', [
            'batches' => DemoData::paginate($batches->values(), 8),
            'statuses' => StatusBadge::options('batch'),
        ]);
    }

    public function create(): View
    {
        return view('admin.batches.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        return redirect()->route('admin.batches.index')->with('success', "Le lot {$data['code']} a été créé. Ajoutez maintenant ses étapes.");
    }

    public function show(int $batch): View
    {
        return view('admin.batches.show', [
            'batch' => DemoData::batchById($batch),
            'stages' => self::STAGES,
            'actors' => DemoData::actors()->pluck('name', 'id')->all(),
        ]);
    }

    public function edit(int $batch): View
    {
        return view('admin.batches.edit', $this->formData() + ['batch' => DemoData::batchById($batch)]);
    }

    public function update(Request $request, int $batch): RedirectResponse
    {
        DemoData::batchById($batch);
        $data = $this->validated($request);

        return redirect()->route('admin.batches.show', $batch)->with('success', "Le lot {$data['code']} a été mis à jour.");
    }

    public function destroy(int $batch): RedirectResponse
    {
        $batch = DemoData::batchById($batch);

        return redirect()->route('admin.batches.index')->with('success', "Le lot {$batch->code} a été supprimé.");
    }

    private function formData(): array
    {
        return [
            'products' => DemoData::products()->pluck('name', 'id')->all(),
            'statuses' => StatusBadge::options('batch'),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^NT-\d{4}-[A-Z]{3}-\d{4}$/'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'string', 'max:60'],
            'production_date' => ['required', 'date'],
            'status' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('batch')))],
        ], ['code.regex' => 'Le code de lot doit suivre le format NT-AAAA-XXX-0000.']);
    }
}
