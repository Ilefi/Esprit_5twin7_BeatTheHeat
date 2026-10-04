<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = StatusBadge::options('review');
        $status = array_key_exists((string) $request->query('statut'), $statuses) ? $request->query('statut') : null;
        $search = trim((string) $request->query('q'));

        $reviews = Review::with(['product', 'user'])
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when((int) $request->query('note'), fn (Builder $query, int $rating) => $query->where('rating', $rating))
            ->when((int) $request->query('produit'), fn (Builder $query, int $product) => $query->where('product_id', $product))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")
                ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))))
            ->latest();

        $counts = Review::pluck('status')->countBy();

        return view('admin.reviews.index', [
            'reviews' => $reviews->paginate(10)->withQueryString(),
            'statuses' => $statuses,
            'status' => $status,
            'counts' => $counts,
            'total' => $counts->sum(),
            'products' => Product::orderBy('id')->pluck('name', 'id')->all(),
        ]);
    }

    public function show(int $review): View
    {
        $review = Review::with([
            'product' => fn ($query) => $query->withRating()->with('producer'),
            'user' => fn ($query) => $query->withCount('reviews'),
            'history',
        ])->findOrFail($review);

        return view('admin.reviews.show', [
            'review' => $review,
            'otherReviews' => $review->user->reviews()->whereKeyNot($review->id)->with('product')->latest()->get(),
        ]);
    }

    public function moderate(Request $request, int $review): RedirectResponse
    {
        Review::findOrFail($review);

        $data = $request->validate([
            'status' => ['required', 'in:published,rejected,flagged'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // TODO(Gestion 4): $review->update(['status' => $data['status']]) + log moderation history
        return back()->with('success', 'Avis '.mb_strtolower(StatusBadge::labelFor('review', $data['status'])).' avec succès.');
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
        Review::findOrFail($review);

        return redirect()->route('admin.reviews.index')->with('success', 'Avis supprimé définitivement.');
    }
}
