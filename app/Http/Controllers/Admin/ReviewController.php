<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 4): replace DemoData with Review::with('product', 'user')->filter($request)->paginate()
        $all = DemoData::reviews();
        $statuses = StatusBadge::options('review');
        $status = array_key_exists($request->query('statut'), $statuses) ? $request->query('statut') : null;

        $reviews = $status ? $all->where('status', $status) : $all;
        if ($rating = (int) $request->query('note')) {
            $reviews = $reviews->where('rating', $rating);
        }
        if ($product = (int) $request->query('produit')) {
            $reviews = $reviews->filter(fn ($r) => $r->product->id === $product);
        }
        if ($search = trim((string) $request->query('q'))) {
            $reviews = $reviews->filter(fn ($r) => str_contains(mb_strtolower($r->title.' '.$r->body.' '.$r->user->name), mb_strtolower($search)));
        }

        return view('admin.reviews.index', [
            'reviews' => DemoData::paginate($reviews->sortByDesc('created_at')->values(), 10),
            'statuses' => $statuses,
            'status' => $status,
            'counts' => $all->countBy('status'),
            'total' => $all->count(),
            'products' => DemoData::products()->pluck('name', 'id')->all(),
        ]);
    }

    public function show(int $review): View
    {
        $review = DemoData::review($review);

        return view('admin.reviews.show', [
            'review' => $review,
            'otherReviews' => DemoData::reviews()->filter(fn ($r) => $r->user->id === $review->user->id && $r->id !== $review->id)->values(),
        ]);
    }

    public function moderate(Request $request, int $review): RedirectResponse
    {
        DemoData::review($review);

        $data = $request->validate([
            'status' => ['required', 'in:published,rejected,flagged'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // TODO(Gestion 4): $review->update(['status' => $data['status']]) + log moderation history
        return back()->with('success', 'Avis '.mb_strtolower(StatusBadge::label('review', $data['status'])).' avec succès.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:published,rejected'],
        ], ['ids.required' => 'Sélectionnez au moins un avis.']);

        $count = count($data['ids']);
        $verb = $data['action'] === 'published' ? 'approuvé' : 'rejeté';

        return back()->with('success', "{$count} avis {$verb}".($count > 1 ? 's' : '').'.');
    }

    public function destroy(int $review): RedirectResponse
    {
        DemoData::review($review);

        return redirect()->route('admin.reviews.index')->with('success', 'Avis supprimé définitivement.');
    }
}
