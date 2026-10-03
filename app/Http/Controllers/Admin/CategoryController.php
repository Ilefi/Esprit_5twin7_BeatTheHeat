<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        // TODO(Gestion 1): replace DemoData with Category::withCount('products')->get()
        return view('admin.categories.index', [
            'categories' => DemoData::categories(),
            'icons' => self::ICONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('createCategory', $this->rules());

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$data['name']} » a été créée.");
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $data = $request->validateWithBag('editCategory'.$category, $this->rules());

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$data['name']} » a été mise à jour.");
    }

    public function destroy(int $category): RedirectResponse
    {
        $category = DemoData::categories()->firstWhere('id', $category) ?? abort(404);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été supprimée.");
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'icon' => ['required', 'in:'.implode(',', array_keys(self::ICONS))],
        ];
    }
}
