<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertificationRequest;
use App\Models\Certification;
use App\Models\CertificationVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CertificationController extends Controller
{
    public const TYPES = [
        'bio' => 'Biologique',
        'local' => 'Local / circuit court',
        'fair' => 'Équitable',
        'origin' => 'Origine protégée',
        'no_pesticide' => 'Sans pesticides',
        'reasoned' => 'Agriculture raisonnée',
    ];

    public function index(): View
    {
        return view('admin.certifications.index', [
            'certifications' => Certification::withCount(['products', 'actors'])->get(),
            'pendingCount' => CertificationVerification::where('status', 'pending')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.certifications.create', ['types' => self::TYPES]);
    }

    public function store(CertificationRequest $request): RedirectResponse
    {
        $certification = Certification::create($request->certificationData());

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$certification->name} » a été créée.");
    }

    public function edit(int $certification): View
    {
        return view('admin.certifications.edit', [
            'certification' => Certification::findOrFail($certification),
            'types' => self::TYPES,
        ]);
    }

    public function update(CertificationRequest $request, int $certification): RedirectResponse
    {
        $certification = Certification::findOrFail($certification);
        $certification->update($request->certificationData());

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$certification->name} » a été mise à jour.");
    }

    public function destroy(int $certification): RedirectResponse
    {
        $certification = Certification::withCount('reports')->findOrFail($certification);

        // Citizen reports keep pointing at their target, so a reported certification cannot be deleted.
        if ($certification->reports_count) {
            return back()->with('error', "La certification « {$certification->name} » fait l'objet de signalements et ne peut pas être supprimée.");
        }

        // Product and actor links and pending verifications are removed by the cascading foreign keys.
        $certification->delete();

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$certification->name} » a été supprimée.");
    }
}
