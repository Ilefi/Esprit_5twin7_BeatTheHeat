<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificationVerification;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificationVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $all = CertificationVerification::with(['actor', 'certification'])->latest('submitted_at')->get();
        $status = array_key_exists($request->query('statut'), StatusBadge::options('verification')) ? $request->query('statut') : 'pending';

        return view('admin.certifications.verifications', [
            'verifications' => $all->where('status', $status)->values(),
            'status' => $status,
            'statuses' => StatusBadge::options('verification'),
            'counts' => $all->countBy('status'),
        ]);
    }

    public function approve(int $verification): RedirectResponse
    {
        $verification = $this->find($verification);

        return redirect()->route('admin.certifications.verifications')
            ->with('success', "Certificat « {$verification->certification->short_name} » de {$verification->actor->name} approuvé.");
    }

    public function reject(Request $request, int $verification): RedirectResponse
    {
        $verification = $this->find($verification);

        $request->validateWithBag('reject'.$verification->id, [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return redirect()->route('admin.certifications.verifications')
            ->with('success', "Certificat de {$verification->actor->name} refusé. Le motif lui a été transmis.");
    }

    private function find(int $id): CertificationVerification
    {
        return CertificationVerification::with(['actor', 'certification'])->findOrFail($id);
    }
}
