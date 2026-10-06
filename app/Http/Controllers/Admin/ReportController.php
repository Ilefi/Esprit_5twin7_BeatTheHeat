<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportAdminRequest;
use App\Models\Report;
use App\Models\User;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const PERIODS = ['7' => '7 derniers jours', '30' => '30 derniers jours', '90' => '3 derniers mois'];

    public function index(Request $request): View
    {
        $types = StatusBadge::options('report_type');
        $statuses = StatusBadge::options('report');
        $priorities = StatusBadge::options('priority');
        $type = (string) $request->query('type');
        $status = (string) $request->query('statut');
        $priority = (string) $request->query('priorite');
        $period = (string) $request->query('periode');
        $kanban = $request->query('vue') === 'kanban';

        $reports = Report::withTarget()->with('assignee')
            ->when(array_key_exists($type, $types), fn(Builder $query) => $query->where('type', $type))
            ->when(array_key_exists($status, $statuses), fn(Builder $query) => $query->where('status', $status))
            ->when(array_key_exists($priority, $priorities), fn(Builder $query) => $query->where('priority', $priority))
            ->when(array_key_exists($period, self::PERIODS), fn(Builder $query) => $query->where('created_at', '>=', now()->subDays((int) $period)))
            // Most urgent first, then newest
            ->orderByRaw("case priority when 'critical' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->latest();

        $reports = $kanban ? $reports->get() : $reports->paginate(10)->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'columns' => $kanban ? $reports->groupBy('status') : null,
            'view' => $kanban ? 'kanban' : 'table',
            'types' => $types,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'periods' => self::PERIODS,
        ]);
    }

    public function show(string $ref): View
    {
        return view('admin.reports.show', [
            'report' => Report::where('ref', $ref)
                ->withTarget()
                ->with(['reporter' => fn($query) => $query->withCount('reports'), 'assignee', 'evidence', 'messages.user', 'notes.user', 'history'])
                ->firstOrFail(),
            'statuses' => StatusBadge::options('report'),
            'priorities' => StatusBadge::options('priority'),
            'moderators' => User::where('role', 'admin')->orderBy('id')->pluck('name', 'id')->all(),
        ]);
    }

    public function update(UpdateReportAdminRequest $request, string $ref): RedirectResponse
    {
        $report = Report::where('ref', $ref)->firstOrFail();

        $validated = $request->validated();

        $oldStatus = $report->status;
        $report->update($validated);

        if ($oldStatus !== $validated['status']) {
            $report->history()->create([
                'label' => 'Statut changé : ' . StatusBadge::labelFor('report', $validated['status']),
                'author' => $request->user()->name,
            ]);
        }

        return redirect()->route('admin.reports.show', $report->ref)->with('success', "Signalement {$report->ref} mis à jour.");
    }

    public function status(Request $request, string $ref): RedirectResponse
    {
        $report = Report::where('ref', $ref)->firstOrFail();
        $data = $request->validate(['status' => ['required', 'in:' . implode(',', array_keys(StatusBadge::options('report')))]]);

        $oldStatus = $report->status;
        $report->update(['status' => $data['status']]);

        if ($oldStatus !== $data['status']) {
            $report->history()->create([
                'label' => 'Statut changé : ' . StatusBadge::labelFor('report', $data['status']),
                'author' => $request->user()->name,
            ]);
        }

        return back()->with('success', "{$report->ref} → " . StatusBadge::labelFor('report', $data['status']) . '.');
    }

    public function note(Request $request, string $ref): RedirectResponse
    {
        $report = Report::where('ref', $ref)->firstOrFail();
        $validated = $request->validateWithBag('note', ['note' => ['required', 'string', 'min:3', 'max:1000']]);

        $report->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['note'],
        ]);

        return redirect()->route('admin.reports.show', $report->ref)->with('success', 'Note interne ajoutée.');
    }

    public function reply(Request $request, string $ref): RedirectResponse
    {
        $report = Report::where('ref', $ref)->firstOrFail();
        $validated = $request->validateWithBag('reply', ['reply' => ['required', 'string', 'min:10', 'max:2000']]);

        $report->messages()->create([
            'user_id' => $request->user()->id,
            'role' => 'moderator',
            'body' => $validated['reply'],
            'attachments' => [],
        ]);

        $report->history()->create([
            'label' => 'Réponse officielle du modérateur',
            'author' => $request->user()->name,
        ]);

        return redirect()->route('admin.reports.show', $report->ref)->with('success', "Réponse envoyée à {$report->reporter->name}.");
    }
}
