<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Certification;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type');
        $search = trim((string) $request->query('q'));

        $actors = Actor::withStats()->with('certifications')
            ->when(array_key_exists($type, StatusBadge::options('actor_type')), fn (Builder $query) => $query->where('type', $type))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")))
            ->orderBy('id');

        return view('admin.actors.index', [
            'actors' => $actors->paginate(8)->withQueryString(),
            'types' => StatusBadge::options('actor_type'),
        ]);
    }

    public function create(): View
    {
        return view('admin.actors.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        return redirect()->route('admin.actors.index')->with('success', "L'acteur « {$data['name']} » a été créé.");
    }

    public function edit(int $actor): View
    {
        return view('admin.actors.edit', $this->formData() + ['actor' => Actor::with('certifications')->findOrFail($actor)]);
    }

    public function update(Request $request, int $actor): RedirectResponse
    {
        Actor::findOrFail($actor);
        $data = $this->validated($request);

        return redirect()->route('admin.actors.index')->with('success', "L'acteur « {$data['name']} » a été mis à jour.");
    }

    public function destroy(int $actor): RedirectResponse
    {
        $actor = Actor::findOrFail($actor);

        return redirect()->route('admin.actors.index')->with('success', "L'acteur « {$actor->name} » a été supprimé.");
    }

    private function formData(): array
    {
        return [
            'types' => StatusBadge::options('actor_type'),
            'certifications' => Certification::orderBy('id')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('actor_type')))],
            'city' => ['required', 'string', 'max:80'],
            'region' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:150'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['integer'],
        ]);
    }
}
