<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 1): replace DemoData with Product::with(...)->filter($request)->paginate()
        $products = DemoData::products();

        if ($search = trim((string) $request->query('q'))) {
            $products = $products->filter(fn ($p) => str_contains(mb_strtolower($p->name.' '.$p->producer->name), mb_strtolower($search)));
        }
        if ($category = (int) $request->query('categorie')) {
            $products = $products->filter(fn ($p) => $p->category->id === $category);
        }
        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('product'))) {
            $products = $products->where('status', $status);
        }
        if (in_array($eco = (string) $request->query('eco'), ['A', 'B', 'C', 'D', 'E'], true)) {
            $products = $products->where('eco_score', $eco);
        }

        return view('admin.products.index', [
            'products' => DemoData::paginate($products->values(), 8),
            'categories' => DemoData::categories()->pluck('name', 'id')->all(),
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
        $product = DemoData::productById($product);

        return view('admin.products.show', [
            'product' => $product,
            'batches' => DemoData::batches()->filter(fn ($b) => $b->product->id === $product->id)->values(),
            'reviews' => DemoData::reviews()->filter(fn ($r) => $r->product->id === $product->id)->sortByDesc('created_at')->take(4)->values(),
            'reports' => DemoData::reports()->filter(fn ($r) => $r->target->type === 'product' && $r->target->id === $product->id)->values(),
        ]);
    }

    public function edit(int $product): View
    {
        return view('admin.products.edit', $this->formData() + ['product' => DemoData::productById($product)]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        DemoData::productById($product);
        $data = $this->validated($request);

        // TODO(Gestion 1): $product->update($data) + sync certifications
        return redirect()->route('admin.products.show', $product)->with('success', "Le produit « {$data['name']} » a été mis à jour.");
    }

    public function destroy(int $product): RedirectResponse
    {
        $product = DemoData::productById($product);

        // TODO(Gestion 1): $product->delete()
        return redirect()->route('admin.products.index')->with('success', "Le produit « {$product->name} » a été supprimé.");
    }

    private function formData(): array
    {
        return [
            'categories' => DemoData::categories()->pluck('name', 'id')->all(),
            'producers' => DemoData::actors()->where('type', 'producer')->pluck('name', 'id')->all(),
            'certifications' => DemoData::certifications(),
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
