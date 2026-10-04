<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmissionFactor;
use App\Models\Impact;
use App\Models\Product;
use App\Support\EcoScore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpactController extends Controller
{
    public function index(Request $request): View
    {
        $eco = (string) $request->query('eco');

        $products = Product::whereHas('impact', fn (Builder $query) => $query->when(
            in_array($eco, ['A', 'B', 'C', 'D', 'E'], true),
            fn (Builder $query) => $query->where('eco_score', $eco),
        ))->with(['impact', 'category'])->orderByEcoPoints()->orderBy('id');

        return view('admin.impacts.index', [
            'products' => $products->paginate(8)->withQueryString(),
            'distribution' => Impact::pluck('eco_score')->countBy(),
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
        return view('admin.impacts.edit', $this->formData() + ['product' => Product::with('impact')->findOrFail($impact)]);
    }

    public function update(Request $request, int $impact): RedirectResponse
    {
        $product = Product::findOrFail($impact);
        $grade = $this->grade($this->validated($request));

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$product->name} » mise à jour — éco-score : {$grade}.");
    }

    public function destroy(int $impact): RedirectResponse
    {
        $product = Product::findOrFail($impact);

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$product->name} » supprimée.");
    }

    public function factors(): View
    {
        return view('admin.impacts.factors', [
            'factors' => EmissionFactor::orderBy('id')->get()->groupBy('category'),
        ]);
    }

    private function formData(): array
    {
        return [
            'products' => Product::orderBy('id')->pluck('name', 'id')->all(),
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
