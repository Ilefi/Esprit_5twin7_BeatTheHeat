<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\View\View;

class CertificationController extends Controller
{
    public function index(): View
    {
        // TODO(Gestion 1): replace DemoData with Certification::withCount('products')->get()
        return view('front.certifications.index', [
            'certifications' => DemoData::certifications(),
        ]);
    }

    public function show(string $slug): View
    {
        // TODO(Gestion 1): replace DemoData with Certification::where('slug', $slug)->with('products')->firstOrFail()
        $certification = DemoData::certification($slug);

        return view('front.certifications.show', [
            'certification' => $certification,
            'products' => DemoData::products()->filter(fn ($p) => $p->status === 'published' && $p->certifications->contains('id', $certification->id))->values(),
            'actors' => DemoData::actors()->filter(fn ($a) => $a->certifications->contains('id', $certification->id))->values(),
            'others' => DemoData::certifications()->where('id', '!=', $certification->id)->values(),
        ]);
    }
}
