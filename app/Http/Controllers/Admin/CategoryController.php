<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->get(),
            'icons' => self::ICONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($request->validateWithBag('createCategory', $this->rules()));

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été créée.");
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $category = Category::findOrFail($category);
        $category->update($request->validateWithBag('editCategory'.$category->id, $this->rules($category)));

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

    private function rules(?Category $category = null): array
    {
        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('categories')->ignore($category)],
            'icon' => ['required', 'in:'.implode(',', array_keys(self::ICONS))],
        ];
    }
}
