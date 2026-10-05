<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $all = $request->user()->reviews()->with(['product.producer', 'user'])->latest()->get();
        $reviews = $all;

        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('review'))) {
            $reviews = $reviews->where('status', $status);
        }

        return view('account.reviews.index', [
            'reviews' => $reviews->values(),
            'statuses' => StatusBadge::options('review'),
            'counts' => $all->countBy('status'),
            'total' => $all->count(),
        ]);
    }

    public function update(Request $request, int $review): RedirectResponse
    {
        $userReview = $request->user()->reviews()->findOrFail($review);

        $validated = $request->validateWithBag('editReview'.$review, [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:1500'],
        ]);

        $userReview->update([
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => 'pending',
        ]);

        $userReview->history()->create([
            'label' => 'Avis modifié par l\'auteur (remis en modération)',
            'author' => $request->user()->name,
        ]);

        return redirect()->route('account.reviews.index')->with('success', 'Votre avis a été modifié. Il repasse en modération avant republication.');
    }

    public function destroy(Request $request, int $review): RedirectResponse
    {
        $userReview = $request->user()->reviews()->findOrFail($review);
        $userReview->delete();

        return redirect()->route('account.reviews.index')->with('success', 'Votre avis a été supprimé.');
    }
}
