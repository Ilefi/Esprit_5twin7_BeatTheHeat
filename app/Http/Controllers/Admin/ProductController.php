<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Actor;
use App\Models\Category;
use App\Models\Certification;
use App\Models\Product;
use App\Services\OpenFoodFacts;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('statut');
        $eco = (string) $request->query('eco');

        $products = Product::with(['category', 'producer', 'certifications', 'impact'])
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhereHas('producer', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))))
            ->when((int) $request->query('categorie'), fn (Builder $query, int $category) => $query->where('category_id', $category))
            ->when(array_key_exists($status, StatusBadge::options('product')), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($eco, ['A', 'B', 'C', 'D', 'E'], true), fn (Builder $query) => $query->whereHas('impact', fn (Builder $query) => $query->where('eco_score', $eco)))
            ->orderBy('id');

        return view('admin.products.index', [
            'products' => $products->paginate(8)->withQueryString(),
            'categories' => Category::orderBy('id')->pluck('name', 'id')->all(),
            'statuses' => StatusBadge::options('product'),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['certifications', 'image']);
        $data['image'] = $request->file('image')->store('products', 'public');

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);
            $product->certifications()->sync($request->validated('certifications', []));

            return $product;
        });

        return redirect()->route('admin.products.show', $product->id)->with('success', "Le produit « {$product->name} » a été créé.");
    }

    public function show(int $product): View
    {
        $product = Product::with(['category', 'producer', 'processor', 'certifications', 'impact'])->withRating()->findOrFail($product);

        return view('admin.products.show', [
            'product' => $product,
            'batches' => $product->batches()->with('steps')->orderBy('id')->get(),
            'reviews' => $product->reviews()->with('user')->latest()->take(4)->get(),
            'reports' => $product->reports()->withTarget()->orderBy('id')->get(),
        ]);
    }

    public function edit(int $product): View
    {
        return view('admin.products.edit', $this->formData() + ['product' => Product::with(['category', 'producer', 'certifications'])->findOrFail($product)]);
    }

    public function update(ProductRequest $request, int $product): RedirectResponse
    {
        $product = Product::findOrFail($product);
        $data = $request->safe()->except(['certifications', 'image']);
        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($data);
            $product->certifications()->sync($request->validated('certifications', []));
        });

        if ($oldImage && $oldImage !== $product->image) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.products.show', $product->id)->with('success', "Le produit « {$product->name} » a été mis à jour.");
    }

    public function destroy(int $product): RedirectResponse
    {
        $product = Product::withCount('reports')->findOrFail($product);

        // Citizen reports keep pointing at their target: the product is unpublished instead of deleted.
        if ($product->reports_count) {
            return back()->with('error', "Le produit « {$product->name} » fait l'objet de signalements : passez-le en brouillon plutôt que de le supprimer.");
        }

        $product->delete();
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return redirect()->route('admin.products.index')->with('success', "Le produit « {$product->name} » a été supprimé.");
    }

    /**
     * Pre-fills the product form from Open Food Facts (JSON for the ntBarcodeImport Alpine component).
     * The category is suggested by matching the OFF categories of the product with our categories' off_tag.
     */
    public function lookup(string $barcode, OpenFoodFacts $openFoodFacts): JsonResponse
    {
        $data = $openFoodFacts->product($barcode);
        if (! $data) {
            return response()->json(['message' => "Aucun produit trouvé sur Open Food Facts pour le code-barres {$barcode}."], 404);
        }

        $category = Category::whereIn('off_tag', $data['categories'])->orderBy('id')->first();

        return response()->json([
            'fields' => array_filter([
                'name' => $data['name'],
                'format' => $data['quantity'],
                'description' => $data['description'],
                'composition' => $data['ingredients'],
                'category_id' => $category?->id,
            ]),
            'summary' => [
                'name' => $data['name'],
                'brands' => $data['brands'],
                'nutriscore' => $data['nutriscore'],
                'nova' => $data['nova'] ? OpenFoodFacts::NOVA_LABELS[$data['nova']] : null,
                'allergens' => $data['allergens'],
                'category' => $category?->name,
                'url' => $data['url'],
            ],
        ]);
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('id')->pluck('name', 'id')->all(),
            'producers' => Actor::where('type', 'producer')->orderBy('id')->pluck('name', 'id')->all(),
            'certifications' => Certification::orderBy('id')->get(),
            'statuses' => StatusBadge::options('product'),
        ];
    }
}
