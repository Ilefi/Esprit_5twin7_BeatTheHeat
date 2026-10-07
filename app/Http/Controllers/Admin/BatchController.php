<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Batch;
use App\Models\Product;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $status = (string) $request->query('statut');
        $search = trim((string) $request->query('q'));

        $batches = Batch::with(['product', 'steps'])
            ->when(array_key_exists($status, StatusBadge::options('batch')), fn (Builder $query) => $query->where('status', $status))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))))
            ->orderBy('id');

        return view('admin.batches.index', [
            'batches' => $batches->paginate(8)->withQueryString(),
            'statuses' => StatusBadge::options('batch'),
        ]);
    }

    public function create(): View
    {
        return view('admin.batches.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $batch = Batch::create($this->validated($request));

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Le lot {$batch->code} a été créé. Ajoutez maintenant ses étapes.");
    }

    public function show(int $batch): View
    {
        return view('admin.batches.show', [
            'batch' => Batch::with(['product', 'steps.actor'])->findOrFail($batch),
            'stages' => self::STAGES,
            'actors' => Actor::orderBy('id')->pluck('name', 'id')->all(),
        ]);
    }

    public function edit(int $batch): View
    {
        return view('admin.batches.edit', $this->formData() + ['batch' => Batch::with('product')->findOrFail($batch)]);
    }

    public function update(Request $request, int $batch): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);
        $batch->update($this->validated($request, $batch->id));

        return redirect()->route('admin.batches.show', $batch->id)
            ->with('success', "Le lot {$batch->code} a été mis à jour.");
    }

    public function destroy(int $batch): RedirectResponse
    {
        $batch = Batch::findOrFail($batch);

        DB::transaction(function () use ($batch) {
            $batch->steps()->delete();
            $batch->delete();
        });

        return redirect()->route('admin.batches.index')
            ->with('success', "Le lot {$batch->code} a été supprimé.");
    }

    private function formData(): array
    {
        return [
            'products' => Product::orderBy('id')->pluck('name', 'id')->all(),
            'statuses' => StatusBadge::options('batch'),
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^NT-\d{4}-[A-Z]{3}-\d{4}$/', Rule::unique('batches', 'code')->ignore($ignoreId)],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'string', 'max:60'],
            'production_date' => ['required', 'date'],
            'status' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('batch')))],
        ], ['code.regex' => 'Le code de lot doit suivre le format NT-AAAA-XXX-0000.']);
    }
}