<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Certification;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\EcoScore;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('front.home', [
            'featuredProducts' => Product::published()->forCards()->orderByDesc('rating_avg')->orderBy('id')->take(8)->get(),
            'certifications' => Certification::withCount(['products', 'actors'])->get(),
            'grades' => EcoScore::grades(),
            'testimonials' => Testimonial::all(),
            'faqs' => Faq::orderBy('position')->get(),
            'exampleBatch' => Batch::orderBy('id')->first(),
            'counters' => [
                ['value' => 1280, 'label' => 'produits tracés', 'icon' => 'fa-qrcode'],
                ['value' => 342, 'label' => 'producteurs engagés', 'icon' => 'fa-tractor'],
                ['value' => 516, 'label' => 'certifications vérifiées', 'icon' => 'fa-award'],
                ['value' => 97, 'label' => 'signalements traités', 'icon' => 'fa-flag'],
            ],
        ]);
    }
}
