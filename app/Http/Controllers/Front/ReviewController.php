<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $validated = $request->validateWithBag('review', [
            'rating' => ['required', 'integer', 'between:1,5'],
            'quality_rating' => ['required', 'integer', 'between:1,5'],
            'transparency_rating' => ['required', 'integer', 'between:1,5'],
            'value_rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:1500'],
        ]);

        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'quality_rating' => $validated['quality_rating'],
            'transparency_rating' => $validated['transparency_rating'],
            'value_rating' => $validated['value_rating'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => 'pending',
        ]);

        $review->history()->create([
            'label' => 'Avis soumis par le consommateur',
            'author' => $request->user()->name,
        ]);

        return redirect(route('front.products.show', $product->slug).'#avis')
            ->with('success', 'Merci ! Votre avis a été envoyé et sera publié après une vérification rapide.');
    }

    public function helpful(int $review): RedirectResponse
    {
        $reviewModel = Review::findOrFail($review);
        $reviewModel->increment('helpful_count');

        return back()->with('success', 'Merci, votre vote « utile » a été pris en compte.');
    }
}
