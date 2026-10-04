<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\CertificationVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$data['name']} » a été créée.");
    }

    public function edit(int $certification): View
    {
        return view('admin.certifications.edit', [
            'certification' => Certification::findOrFail($certification),
            'types' => self::TYPES,
        ]);
    }

    public function update(Request $request, int $certification): RedirectResponse
    {
        Certification::findOrFail($certification);
        $data = $this->validated($request);

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$data['name']} » a été mise à jour.");
    }

    public function destroy(int $certification): RedirectResponse
    {
        $certification = Certification::findOrFail($certification);

        return redirect()->route('admin.certifications.index')->with('success', "La certification « {$certification->name} » a été supprimée.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['required', 'string', 'max:30'],
            'type' => ['required', 'in:'.implode(',', array_keys(self::TYPES))],
            'issuer' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:1500'],
            'criteria' => ['nullable', 'string', 'max:2000'],
        ], [], ['short_name' => 'nom court']);
    }
}
