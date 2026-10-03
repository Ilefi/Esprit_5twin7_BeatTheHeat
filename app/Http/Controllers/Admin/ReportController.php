<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const PERIODS = ['7' => '7 derniers jours', '30' => '30 derniers jours', '90' => '3 derniers mois'];

    public function index(Request $request): View
    {
        // TODO(Gestion 4): replace DemoData with Report::with('target', 'assignee')->filter($request)->paginate()
        $reports = DemoData::reports();
        $types = StatusBadge::options('report_type');
        $statuses = StatusBadge::options('report');
        $priorities = StatusBadge::options('priority');

        if (array_key_exists($type = (string) $request->query('type'), $types)) {
            $reports = $reports->where('type', $type);
        }
        if (array_key_exists($status = (string) $request->query('statut'), $statuses)) {
            $reports = $reports->where('status', $status);
        }
        if (array_key_exists($priority = (string) $request->query('priorite'), $priorities)) {
            $reports = $reports->where('priority', $priority);
        }
        if (array_key_exists($period = (string) $request->query('periode'), self::PERIODS)) {
            $reports = $reports->filter(fn ($r) => $r->created_at->gte(now()->subDays((int) $period)));
        }

        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        $reports = $reports->sortBy([fn ($a, $b) => $priorityOrder[$a->priority] <=> $priorityOrder[$b->priority], fn ($a, $b) => $b->created_at <=> $a->created_at])->values();

        return view('admin.reports.index', [
            'reports' => $request->query('vue') === 'kanban' ? $reports : DemoData::paginate($reports, 10),
            'columns' => $request->query('vue') === 'kanban' ? $reports->groupBy('status') : null,
            'view' => $request->query('vue') === 'kanban' ? 'kanban' : 'table',
            'types' => $types,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'periods' => self::PERIODS,
        ]);
    }

    public function show(string $ref): View
    {
        return view('admin.reports.show', [
            'report' => DemoData::report($ref),
            'statuses' => StatusBadge::options('report'),
            'priorities' => StatusBadge::options('priority'),
            'moderators' => DemoData::users()->where('role', 'admin')->pluck('name', 'id')->all(),
        ]);
    }

    public function update(Request $request, string $ref): RedirectResponse
    {
        $report = DemoData::report($ref);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('report')))],
            'priority' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('priority')))],
            'assignee_id' => ['nullable', 'integer'],
            'resolution' => ['nullable', 'required_if:status,confirmed,rejected,resolved', 'string', 'max:1000'],
        ], ['resolution.required_if' => 'Une décision motivée est requise pour clôturer le signalement.'], ['resolution' => 'décision']);

        // TODO(Gestion 4): $report->update([...]) + history entry + notify reporter
        return redirect()->route('admin.reports.show', $report->ref)->with('success', "Signalement {$report->ref} mis à jour.");
    }

    public function status(Request $request, string $ref): RedirectResponse
    {
        $report = DemoData::report($ref);
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('report')))]]);

        return back()->with('success', "{$report->ref} → ".StatusBadge::label('report', $data['status']).'.');
    }

    public function note(Request $request, string $ref): RedirectResponse
    {
        $report = DemoData::report($ref);
        $request->validateWithBag('note', ['note' => ['required', 'string', 'min:3', 'max:1000']]);

        return redirect()->route('admin.reports.show', $report->ref)->with('success', 'Note interne ajoutée.');
    }

    public function reply(Request $request, string $ref): RedirectResponse
    {
        $report = DemoData::report($ref);
        $request->validateWithBag('reply', ['reply' => ['required', 'string', 'min:10', 'max:2000']]);

        return redirect()->route('admin.reports.show', $report->ref)->with('success', "Réponse envoyée à {$report->reporter->name}.");
    }
}
