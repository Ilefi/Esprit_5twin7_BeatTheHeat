<?php

namespace App\Http\Controllers\Account;

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
        // TODO(Gestion 4): replace DemoData with $request->user()->reviews()->latest()->paginate()
        $all = DemoData::accountReviews();
        $reviews = $all;

        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('review'))) {
            $reviews = $reviews->where('status', $status);
        }

        return view('account.reviews.index', [
            'reviews' => $reviews->sortByDesc('created_at')->values(),
            'statuses' => StatusBadge::options('review'),
            'counts' => $all->countBy('status'),
            'total' => $all->count(),
        ]);
    }

    public function update(Request $request, int $review): RedirectResponse
    {
        DemoData::review($review);

        $request->validateWithBag('editReview'.$review, [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:1500'],
        ]);

        // TODO(Gestion 4): $review->update([...] + ['status' => 'pending'])
        return redirect()->route('account.reviews.index')->with('success', 'Votre avis a été modifié. Il repasse en modération avant republication.');
    }

    public function destroy(int $review): RedirectResponse
    {
        DemoData::review($review);

        // TODO(Gestion 4): $review->delete()
        return redirect()->route('account.reviews.index')->with('success', 'Votre avis a été supprimé.');
    }
}
