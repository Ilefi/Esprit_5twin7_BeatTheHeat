<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Impact;
use App\Services\CatalogAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public const ICONS = [
        'fa-bottle-droplet' => 'Bouteille',
        'fa-apple-whole' => 'Fruit',
        'fa-seedling' => 'Pousse',
        'fa-wheat-awn' => 'Épi de blé',
        'fa-jar' => 'Bocal',
        'fa-cheese' => 'Fromage',
        'fa-carrot' => 'Légume',
        'fa-fish' => 'Poisson',
    ];

    public function index(): View
    {
        $categories = Category::withCount('products')
            ->withAvg('impacts as eco_avg', 'eco_points')
            ->with('substitutes')
            ->orderBy('id')
            ->get();

        // Eco-score distribution per category: [category_id => ['A' => n, …]]
        $grades = Impact::join('products', 'products.id', '=', 'impacts.product_id')
            ->selectRaw('products.category_id, impacts.eco_score, count(*) as total')
            ->groupBy('products.category_id', 'impacts.eco_score')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => $rows->pluck('total', 'eco_score')->map(fn ($total) => (int) $total)->all());

        return view('admin.categories.index', [
            'categories' => $categories,
            'grades' => $grades,
            'icons' => self::ICONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('createCategory', $this->rules(), $this->messages());
        $category = Category::create(Arr::except($data, 'substitutes'));
        $category->syncSubstitutes($data['substitutes'] ?? []);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été créée.");
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $category = Category::findOrFail($category);
        $data = $request->validateWithBag('editCategory'.$category->id, $this->rules($category), $this->messages());
        $category->update(Arr::except($data, 'substitutes'));
        $category->syncSubstitutes($data['substitutes'] ?? []);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été mise à jour.");
    }

    public function destroy(int $category): RedirectResponse
    {
        $category = Category::withCount('products')->findOrFail($category);

        if ($category->products_count) {
            return back()->with('error', "La catégorie « {$category->name} » contient {$category->products_count} produit(s) : reclassez-les avant de la supprimer.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été supprimée.");
    }

    /**
     * Suggests the icon, Open Food Facts tag and substitutes of a category with Gemini (JSON for the ntAiCategoryAssistant Alpine component).
     */
    public function suggest(Request $request, CatalogAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ], ['name.required' => 'Saisissez d\'abord le nom de la catégorie.']);

        if (! $assistant->configured()) {
            return response()->json(['message' => CatalogAssistant::NOT_CONFIGURED], 503);
        }

        $settings = $assistant->categorySettings($data['name'], Category::find($data['category_id'] ?? null));

        return $settings
            ? response()->json($settings)
            : response()->json(['message' => CatalogAssistant::UNAVAILABLE], 502);
    }

    private function rules(?Category $category = null): array
    {
        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('categories')->ignore($category)],
            'icon' => ['required', 'in:'.implode(',', array_keys(self::ICONS))],
            'off_tag' => ['nullable', 'string', 'max:100', 'regex:/^[a-z]{2}:[a-z0-9-]+$/'],
            'substitutes' => ['nullable', 'array'],
            'substitutes.*' => ['integer', 'distinct', Rule::exists('categories', 'id'), Rule::notIn(array_filter([$category?->id]))],
        ];
    }

    private function messages(): array
    {
        return [
            'off_tag.regex' => 'Le tag Open Food Facts doit avoir la forme « en:honeys » (langue, deux-points, mots en minuscules séparés par des tirets).',
            'substitutes.*.not_in' => 'Une catégorie ne peut pas être sa propre catégorie de remplacement.',
        ];
    }
}
