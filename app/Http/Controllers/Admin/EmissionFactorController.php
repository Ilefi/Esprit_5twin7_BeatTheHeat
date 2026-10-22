<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmissionFactorRequest;
use App\Models\EmissionFactor;
use App\Services\AiAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmissionFactorController extends Controller
{
    public function index(Request $request): View
    {
        $category = (string) $request->query('categorie');
        $search = (string) $request->query('q');

        $query = EmissionFactor::withCount('impacts')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('source', 'like', "%{$search}%"))
            ->orderBy('category')
            ->orderBy('name');

        $categories = EmissionFactor::select('category')->distinct()->pluck('category')->all();

        return view('admin.factors.index', [
            'factors' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'selectedCategory' => $category,
            'search' => $search,
            'totalCount' => EmissionFactor::count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.factors.create', [
            'factor' => null,
            'categories' => EmissionFactorRequest::CATEGORIES,
        ]);
    }

    public function store(EmissionFactorRequest $request): RedirectResponse
    {
        $factor = EmissionFactor::create($request->validated());

        return redirect()->route('admin.factors.index')
            ->with('success', "Le facteur d'émission « {$factor->name} » a été créé avec succès.");
    }

    public function edit(EmissionFactor $factor): View
    {
        return view('admin.factors.edit', [
            'factor' => $factor,
            'categories' => EmissionFactorRequest::CATEGORIES,
        ]);
    }

    public function update(EmissionFactorRequest $request, EmissionFactor $factor): RedirectResponse
    {
        $factor->update($request->validated());

        return redirect()->route('admin.factors.index')
            ->with('success', "Le facteur d'émission « {$factor->name} » a été mis à jour.");
    }

    public function destroy(EmissionFactor $factor): RedirectResponse
    {
        $impactsCount = $factor->impacts()->count();
        if ($impactsCount > 0) {
            // Nullify reference or warn: our foreign key has nullOnDelete, but alert the admin
            $factor->impacts()->update(['emission_factor_id' => null]);
        }

        $factor->delete();

        return redirect()->route('admin.factors.index')
            ->with('success', "Le facteur d'émission « {$factor->name} » a été supprimé.");
    }

    public function aiEstimate(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $aiService->estimateFactor($validated['name'], $validated['category'] ?? null);

        return response()->json($result);
    }
}
