<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 2): replace DemoData with Actor::query()->filter($request)->paginate()
        $actors = DemoData::actors();

        if (array_key_exists($type = (string) $request->query('type'), StatusBadge::options('actor_type'))) {
            $actors = $actors->where('type', $type);
        }
        if ($search = trim((string) $request->query('q'))) {
            $actors = $actors->filter(fn ($a) => str_contains(mb_strtolower($a->name.' '.$a->city.' '.$a->region), mb_strtolower($search)));
        }

        return view('front.actors.index', [
            'actors' => DemoData::paginate($actors->values(), 9),
            'types' => StatusBadge::options('actor_type'),
            'counts' => DemoData::actors()->countBy('type'),
        ]);
    }

    public function show(string $slug): View
    {
        // TODO(Gestion 2): replace DemoData with Actor::where('slug', $slug)->with(...)->firstOrFail()
        $actor = DemoData::actor($slug);

        return view('front.actors.show', [
            'actor' => $actor,
            'products' => DemoData::productsFor($actor)->where('status', 'published')->values(),
            'batches' => DemoData::batchesFor($actor),
        ]);
    }
}
