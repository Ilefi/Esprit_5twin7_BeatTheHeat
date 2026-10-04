<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\View\View;

class CertificationController extends Controller
{
    public function index(): View
    {
        return view('front.certifications.index', [
            'certifications' => Certification::withCount(['products', 'actors'])->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $certification = Certification::where('slug', $slug)->withCount(['products', 'actors'])->firstOrFail();

        return view('front.certifications.show', [
            'certification' => $certification,
            'products' => $certification->products()->published()->forCards()->orderBy('products.id')->get(),
            'actors' => $certification->actors()->withStats()->with('certifications')->orderBy('actors.id')->get(),
            'others' => Certification::whereKeyNot($certification->id)->withCount(['products', 'actors'])->get(),
        ]);
    }
}
