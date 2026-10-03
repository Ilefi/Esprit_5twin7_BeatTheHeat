<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\Support\EcoScore;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        // TODO(Gestion 1-4): replace DemoData with Eloquent queries
        $products = DemoData::products()->where('status', 'published');

        return view('front.home', [
            'featuredProducts' => $products->sortByDesc('rating_avg')->take(8)->values(),
            'certifications' => DemoData::certifications(),
            'grades' => EcoScore::grades(),
            'testimonials' => DemoData::testimonials(),
            'faqs' => DemoData::faqs(),
            'exampleBatch' => DemoData::batches()->first(),
            'counters' => [
                ['value' => 1280, 'label' => 'produits tracés', 'icon' => 'fa-qrcode'],
                ['value' => 342, 'label' => 'producteurs engagés', 'icon' => 'fa-tractor'],
                ['value' => 516, 'label' => 'certifications vérifiées', 'icon' => 'fa-award'],
                ['value' => 97, 'label' => 'signalements traités', 'icon' => 'fa-flag'],
            ],
        ]);
    }
}
