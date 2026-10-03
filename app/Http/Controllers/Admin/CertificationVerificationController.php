<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificationVerificationController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 1): replace DemoData with CertificationRequest::with('actor', 'certification')->latest()->get()
        $all = DemoData::verifications();
        $status = array_key_exists($request->query('statut'), StatusBadge::options('verification')) ? $request->query('statut') : 'pending';

        return view('admin.certifications.verifications', [
            'verifications' => $all->where('status', $status)->sortByDesc('submitted_at')->values(),
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

    private function find(int $id): object
    {
        return DemoData::verifications()->firstWhere('id', $id) ?? abort(404);
    }
}
