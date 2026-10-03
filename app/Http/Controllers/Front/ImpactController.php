<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
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
            'example' => DemoData::product('huile-olive-sfax'),
        ]);
    }

    public function compare(Request $request): View
    {
        // TODO(Gestion 3): replace DemoData with Product::with('impact')->whereIn('id', $ids)->get()
        $all = DemoData::products()->where('status', 'published')->values();

        $ids = collect((array) $request->query('produits', []))->map(fn ($id) => (int) $id)->filter()->unique()->take(3);
        if ($ids->count() < 2) {
            $ids = collect([1, 11]);
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
