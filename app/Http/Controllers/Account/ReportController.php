<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(Gestion 4): replace DemoData with $request->user()->reports()->latest()->paginate()
        $all = DemoData::accountReports();
        $reports = $all;

        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('report'))) {
            $reports = $reports->where('status', $status);
        }

        return view('account.reports.index', [
            'reports' => $reports->sortByDesc('created_at')->values(),
            'statuses' => StatusBadge::options('report'),
            'counts' => $all->countBy('status'),
            'total' => $all->count(),
        ]);
    }

    public function show(string $ref): View
    {
        // TODO(Gestion 4): $request->user()->reports()->where('ref', $ref)->firstOrFail()
        $report = DemoData::accountReports()->firstWhere('ref', $ref) ?? abort(404);

        $stage = match ($report->status) {
            'pending' => 1,
            'in_review' => 2,
            'confirmed', 'rejected' => 3,
            'resolved' => 4,
        };

        return view('account.reports.show', [
            'report' => $report,
            'stage' => $stage,
        ]);
    }

    public function message(Request $request, string $ref): RedirectResponse
    {
        DemoData::accountReports()->firstWhere('ref', $ref) ?? abort(404);

        $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // TODO(Gestion 4): $report->messages()->create([...])
        return redirect()->route('account.reports.show', $ref)->with('success', 'Message envoyé au modérateur.');
    }
}
