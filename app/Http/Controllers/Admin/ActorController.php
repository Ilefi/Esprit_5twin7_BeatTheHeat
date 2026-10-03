<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 2): replace DemoData with Actor::withCount(...)->filter($request)->paginate()
        $actors = DemoData::actors();

        if (array_key_exists($type = (string) $request->query('type'), StatusBadge::options('actor_type'))) {
            $actors = $actors->where('type', $type);
        }
        if ($search = trim((string) $request->query('q'))) {
            $actors = $actors->filter(fn ($a) => str_contains(mb_strtolower($a->name.' '.$a->city), mb_strtolower($search)));
        }

        return view('admin.actors.index', [
            'actors' => DemoData::paginate($actors->values(), 8),
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
        return view('admin.actors.edit', $this->formData() + ['actor' => DemoData::actorById($actor)]);
    }

    public function update(Request $request, int $actor): RedirectResponse
    {
        DemoData::actorById($actor);
        $data = $this->validated($request);

        return redirect()->route('admin.actors.index')->with('success', "L'acteur « {$data['name']} » a été mis à jour.");
    }

    public function destroy(int $actor): RedirectResponse
    {
        $actor = DemoData::actorById($actor);

        return redirect()->route('admin.actors.index')->with('success', "L'acteur « {$actor->name} » a été supprimé.");
    }

    private function formData(): array
    {
        return [
            'types' => StatusBadge::options('actor_type'),
            'certifications' => DemoData::certifications(),
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
