<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public const SORTS = [
        'popular' => 'Les mieux notés',
        'eco' => 'Meilleur éco-score',
        'name' => 'Nom (A → Z)',
        'price_asc' => 'Prix croissant',
        'price_desc' => 'Prix décroissant',
    ];

    public function index(Request $request): View
    {
        // TODO(Gestion 1): replace DemoData with Eloquent (Product::query()->with(...)->filter($request)->paginate())
        $products = DemoData::products()->where('status', 'published');

        if ($search = trim((string) $request->query('q'))) {
            $products = $products->filter(fn ($p) => str_contains(mb_strtolower($p->name.' '.$p->producer->name.' '.$p->region), mb_strtolower($search)));
        }
        if ($category = $request->query('categorie')) {
            $products = $products->filter(fn ($p) => $p->category->slug === $category);
        }
        if ($certification = $request->query('certification')) {
            $products = $products->filter(fn ($p) => $p->certifications->contains('slug', $certification));
        }
        if ($eco = (array) $request->query('eco', [])) {
            $products = $products->filter(fn ($p) => in_array($p->eco_score, $eco, true));
        }
        if ($region = $request->query('region')) {
            $products = $products->where('region', $region);
        }

        $products = match ($request->query('tri', 'popular')) {
            'eco' => $products->sortByDesc(fn ($p) => $p->impact->eco_points),
            'name' => $products->sortBy('name'),
            'price_asc' => $products->sortBy('price'),
            'price_desc' => $products->sortByDesc('price'),
            default => $products->sortByDesc('rating_avg'),
        };

        return view('front.products.index', [
            'products' => DemoData::paginate($products->values(), 9),
            'categories' => DemoData::categories(),
            'certifications' => DemoData::certifications(),
            'regions' => DemoData::products()->pluck('region')->unique()->sort()->values(),
            'sorts' => self::SORTS,
            'layout' => $request->query('vue') === 'liste' ? 'list' : 'grid',
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        // TODO(Gestion 1): replace DemoData with Product::where('slug', $slug)->with(...)->firstOrFail()
        $product = DemoData::product($slug);
        $allReviews = DemoData::reviewsFor($product);

        // TODO(Gestion 4): move review filters into a query scope
        $reviews = $allReviews;
        if ($rating = (int) $request->query('note')) {
            $reviews = $reviews->where('rating', $rating);
        }
        if ($request->boolean('verifie')) {
            $reviews = $reviews->where('verified_purchase', true);
        }
        $reviews = match ($request->query('tri_avis', 'recent')) {
            'useful' => $reviews->sortByDesc('helpful_count'),
            'rating_desc' => $reviews->sortByDesc('rating'),
            'rating_asc' => $reviews->sortBy('rating'),
            default => $reviews->sortByDesc('created_at'),
        };

        $distribution = collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($star) => [$star => $allReviews->where('rating', $star)->count()]);

        return view('front.products.show', [
            'product' => $product,
            'batch' => DemoData::batches()->first(fn ($b) => $b->product->id === $product->id),
            'reviews' => $reviews->values(),
            'reviewStats' => (object) [
                'average' => $allReviews->avg('rating') ?? 0,
                'count' => $allReviews->count(),
                'distribution' => $distribution,
                'quality' => $allReviews->avg('quality_rating') ?? 0,
                'transparency' => $allReviews->avg('transparency_rating') ?? 0,
                'value' => $allReviews->avg('value_rating') ?? 0,
            ],
            'similarProducts' => DemoData::products()
                ->where('status', 'published')
                ->filter(fn ($p) => $p->id !== $product->id && ($p->category->id === $product->category->id || $p->producer->id === $product->producer->id))
                ->take(4)->values(),
        ]);
    }
}
