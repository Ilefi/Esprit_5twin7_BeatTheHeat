<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Certification;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
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
        $search = trim((string) $request->query('q'));
        $grades = array_intersect((array) $request->query('eco', []), ['A', 'B', 'C', 'D', 'E']);

        $products = Product::published()->forCards()
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('region', 'like', "%{$search}%")
                ->orWhereHas('producer', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($request->query('categorie'), fn (Builder $query, $slug) => $query->whereHas('category', fn (Builder $query) => $query->where('slug', $slug)))
            ->when($request->query('certification'), fn (Builder $query, $slug) => $query->whereHas('certifications', fn (Builder $query) => $query->where('slug', $slug)))
            ->when($grades, fn (Builder $query) => $query->whereHas('impact', fn (Builder $query) => $query->whereIn('eco_score', $grades)))
            ->when($request->query('region'), fn (Builder $query, $region) => $query->where('region', $region));

        match ($request->query('tri', 'popular')) {
            'eco' => $products->orderByEcoPoints(),
            'name' => $products->orderBy('name'),
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            default => $products->orderByDesc('rating_avg'),
        };

        return view('front.products.index', [
            'products' => $products->orderBy('id')->paginate(9)->withQueryString(),
            'categories' => Category::withCount('products')->get(),
            'certifications' => Certification::withCount(['products', 'actors'])->get(),
            'regions' => Product::distinct()->orderBy('region')->pluck('region'),
            'sorts' => self::SORTS,
            'layout' => $request->query('vue') === 'liste' ? 'list' : 'grid',
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'producer', 'processor', 'certifications', 'impact'])
            ->withRating()
            ->firstOrFail();

        $allReviews = $product->reviews()->published()->with('user')->get()->each->setRelation('product', $product);

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
            'batch' => $product->firstBatch()->with('steps.actor')->first(),
            'reviews' => $reviews->values(),
            'reviewStats' => (object) [
                'average' => $allReviews->avg('rating') ?? 0,
                'count' => $allReviews->count(),
                'distribution' => $distribution,
                'quality' => $allReviews->avg('quality_rating') ?? 0,
                'transparency' => $allReviews->avg('transparency_rating') ?? 0,
                'value' => $allReviews->avg('value_rating') ?? 0,
            ],
            'similarProducts' => Product::published()->forCards()
                ->whereKeyNot($product->id)
                ->where(fn (Builder $query) => $query->where('category_id', $product->category_id)->orWhere('producer_id', $product->producer_id))
                ->orderBy('id')
                ->take(4)
                ->get(),
        ]);
    }
}
