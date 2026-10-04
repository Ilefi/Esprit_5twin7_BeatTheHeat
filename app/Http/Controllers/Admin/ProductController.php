<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Category;
use App\Models\Certification;
use App\Models\Product;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // TODO(Gestion 1): Product::create($data) + sync certifications
        return redirect()->route('admin.products.index')->with('success', "Le produit « {$data['name']} » a été créé.");
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

    public function update(Request $request, int $product): RedirectResponse
    {
        Product::findOrFail($product);
        $data = $this->validated($request);

        // TODO(Gestion 1): $product->update($data) + sync certifications
        return redirect()->route('admin.products.show', $product)->with('success', "Le produit « {$data['name']} » a été mis à jour.");
    }

    public function destroy(int $product): RedirectResponse
    {
        $product = Product::findOrFail($product);

        // TODO(Gestion 1): $product->delete()
        return redirect()->route('admin.products.index')->with('success', "Le produit « {$product->name} » a été supprimé.");
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

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer'],
            'producer_id' => ['required', 'integer'],
            'region' => ['required', 'string', 'max:80'],
            'format' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0', 'max:10000'],
            'status' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('product')))],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'composition' => ['nullable', 'string', 'max:1000'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['integer'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
