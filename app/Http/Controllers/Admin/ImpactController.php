<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\Support\EcoScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpactController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 3): replace DemoData with Impact::with('product')->filter($request)->paginate()
        $products = DemoData::products();

        if (in_array($eco = (string) $request->query('eco'), ['A', 'B', 'C', 'D', 'E'], true)) {
            $products = $products->where('eco_score', $eco);
        }

        return view('admin.impacts.index', [
            'products' => DemoData::paginate($products->sortByDesc(fn ($p) => $p->impact->eco_points)->values(), 8),
            'distribution' => DemoData::products()->countBy('eco_score'),
            'packaging' => EcoScore::PACKAGING_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('admin.impacts.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $grade = $this->grade($data);

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte enregistrée — éco-score calculé : {$grade}.");
    }

    public function edit(int $impact): View
    {
        return view('admin.impacts.edit', $this->formData() + ['product' => DemoData::productById($impact)]);
    }

    public function update(Request $request, int $impact): RedirectResponse
    {
        $product = DemoData::productById($impact);
        $grade = $this->grade($this->validated($request));

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$product->name} » mise à jour — éco-score : {$grade}.");
    }

    public function destroy(int $impact): RedirectResponse
    {
        $product = DemoData::productById($impact);

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$product->name} » supprimée.");
    }

    public function factors(): View
    {
        // TODO(Gestion 3): replace DemoData with EmissionFactor::orderBy('category')->get()
        return view('admin.impacts.factors', [
            'factors' => DemoData::emissionFactors()->groupBy('category'),
        ]);
    }

    private function formData(): array
    {
        return [
            'products' => DemoData::products()->pluck('name', 'id')->all(),
            'packaging' => EcoScore::PACKAGING_LABELS,
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', 'integer'],
            'co2_per_kg' => ['required', 'numeric', 'min:0', 'max:100'],
            'water_per_kg' => ['required', 'numeric', 'min:0', 'max:50000'],
            'distance_km' => ['required', 'numeric', 'min:0', 'max:20000'],
            'packaging' => ['required', 'in:'.implode(',', array_keys(EcoScore::PACKAGING_LABELS))],
            'seasonal' => ['nullable', 'boolean'],
        ]);
    }

    private function grade(array $data): string
    {
        return EcoScore::grade(EcoScore::points(
            (float) $data['co2_per_kg'], (float) $data['water_per_kg'], (float) $data['distance_km'], $data['packaging'], (bool) ($data['seasonal'] ?? false),
        ));
    }
}
