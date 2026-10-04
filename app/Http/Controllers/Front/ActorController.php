<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Product;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorController extends Controller
{
    public function index(Request $request): View
    {
        $types = StatusBadge::options('actor_type');
        $type = (string) $request->query('type');
        $search = trim((string) $request->query('q'));

        $actors = Actor::withStats()->with('certifications')
            ->when(array_key_exists($type, $types), fn (Builder $query) => $query->where('type', $type))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('region', 'like', "%{$search}%")))
            ->orderBy('id');

        return view('front.actors.index', [
            'actors' => $actors->paginate(9)->withQueryString(),
            'types' => $types,
            'counts' => Actor::pluck('type')->countBy(),
        ]);
    }

    public function show(string $slug): View
    {
        $actor = Actor::where('slug', $slug)->withStats()->with('certifications')->firstOrFail();

        return view('front.actors.show', [
            'actor' => $actor,
            'products' => Product::published()->forCards()
                ->where(fn (Builder $query) => $query->where('producer_id', $actor->id)->orWhere('processor_id', $actor->id))
                ->orderBy('id')
                ->get(),
            'batches' => $actor->batches()->with(['product', 'steps'])->orderBy('id')->get(),
        ]);
    }
}
