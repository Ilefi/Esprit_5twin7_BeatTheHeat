<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImpactRequest;
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
            'missing' => Product::doesntHave('impact')->count(),
            'packaging' => EcoScore::PACKAGING_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('admin.impacts.create', $this->formData() + [
            // One footprint per product: only products without one can be picked.
            'products' => Product::doesntHave('impact')->orderBy('id')->pluck('name', 'id')->all(),
        ]);
    }

    public function store(ImpactRequest $request): RedirectResponse
    {
        $impact = Impact::create($request->impactData());

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$impact->product->name} » enregistrée — éco-score calculé : {$impact->eco_score}.");
    }

    public function edit(int $impact): View
    {
        return view('admin.impacts.edit', $this->formData() + ['product' => $this->productWithImpact($impact)]);
    }

    public function update(ImpactRequest $request, int $impact): RedirectResponse
    {
        $product = $this->productWithImpact($impact);
        $product->impact->update($request->impactData());

        return redirect()->route('admin.impacts.index')->with('success', "Empreinte de « {$product->name} » mise à jour — éco-score : {$product->impact->eco_score}.");
    }

    public function destroy(int $impact): RedirectResponse
    {
        $product = $this->productWithImpact($impact);
        $product->impact->delete();

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
            'packaging' => EcoScore::PACKAGING_LABELS,
            'methodologies' => EcoScore::METHODOLOGIES,
        ];
    }

    /** Footprints are addressed by their product id (/admin/empreinte/{product}/edit). */
    private function productWithImpact(int $product): Product
    {
        return Product::with('impact')->has('impact')->findOrFail($product);
    }
}
