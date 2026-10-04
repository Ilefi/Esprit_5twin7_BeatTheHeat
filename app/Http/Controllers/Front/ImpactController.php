<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\EcoScore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpactController extends Controller
{
    public function index(): View
    {
        return view('front.impact.index', [
            'grades' => EcoScore::grades(),
            'packaging' => EcoScore::PACKAGING_LABELS,
            'example' => Product::published()->has('impact')->with('impact')->orderBy('id')->firstOrFail(),
        ]);
    }

    public function compare(Request $request): View
    {
        $all = Product::published()->has('impact')->with(['impact', 'category', 'producer'])->orderBy('id')->get();

        $ids = collect((array) $request->query('produits', []))->map(fn ($id) => (int) $id)->filter()->unique()->take(3);
        if ($ids->count() < 2) {
            // Default comparison: best vs worst eco-score.
            $byPoints = $all->sortByDesc(fn ($p) => $p->impact->eco_points);
            $ids = collect([$byPoints->first()?->id, $byPoints->last()?->id])->filter()->unique();
        }

        $selected = $ids->map(fn ($id) => $all->firstWhere('id', $id))->filter()->values();
        $best = $selected->sortByDesc(fn ($p) => $p->impact->eco_points)->first();

        return view('front.impact.compare', [
            'products' => $all,
            'selected' => $selected,
            'best' => $best,
            'packaging' => EcoScore::PACKAGING_LABELS,
        ]);
    }
}
