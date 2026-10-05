<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $all = $request->user()->reports()->withTarget()->with('reporter')->latest()->get();
        $reports = $all;

        if (array_key_exists($status = (string) $request->query('statut'), StatusBadge::options('report'))) {
            $reports = $reports->where('status', $status);
        }

        return view('account.reports.index', [
            'reports' => $reports->values(),
            'statuses' => StatusBadge::options('report'),
            'counts' => $all->countBy('status'),
            'total' => $all->count(),
        ]);
    }

    public function show(Request $request, string $ref): View
    {
        $report = $request->user()->reports()
            ->where('ref', $ref)
            ->withTarget()
            ->with(['evidence', 'messages.user', 'history'])
            ->firstOrFail();

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

    public function edit(Request $request, string $ref): View|RedirectResponse
    {
        $report = $request->user()->reports()->where('ref', $ref)->withTarget()->firstOrFail();

        if ($report->status !== 'pending') {
            return redirect()->route('account.reports.show', $ref)
                ->with('error', "Ce signalement est déjà en cours d'examen ou traité et ne peut plus être modifié.");
        }

        $targets = [
            'product' => \App\Models\Product::published()->with('producer')->orderBy('id')->get()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'subtitle' => $p->producer->name]),
            'actor' => \App\Models\Actor::orderBy('id')->get()->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'subtitle' => $a->city]),
            'certification' => \App\Models\Certification::orderBy('id')->get()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'subtitle' => $c->issuer]),
        ];

        return view('account.reports.edit', [
            'report' => $report,
            'types' => collect(StatusBadge::options('report_type'))->map(fn ($label, $value) => [
                'value' => $value, 'label' => $label, 'icon' => StatusBadge::iconFor('report_type', $value),
            ])->values(),
            'targets' => $targets,
        ]);
    }

    public function update(Request $request, string $ref): RedirectResponse
    {
        $report = $request->user()->reports()->where('ref', $ref)->firstOrFail();

        if ($report->status !== 'pending') {
            return redirect()->route('account.reports.show', $ref)
                ->with('error', "Ce signalement est déjà en cours d'examen ou traité et ne peut plus être modifié.");
        }

        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('report_type')))],
            'target_type' => ['required', 'in:product,actor,certification'],
            'target_id' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'min:30', 'max:3000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'links' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'type' => $validated['type'],
            'reportable_type' => $validated['target_type'],
            'reportable_id' => $validated['target_id'],
            'title' => 'Signalement '.StatusBadge::labelFor('report_type', $validated['type']),
            'description' => $validated['description'],
        ]);

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('evidence', 'public');
                $report->evidence()->create([
                    'kind' => 'file',
                    'name' => $file->getClientOriginalName(),
                    'size' => round($file->getSize() / 1024).' Ko',
                    'url' => asset('storage/'.$path),
                ]);
            }
        }

        if (! empty($validated['links'])) {
            $report->evidence()->create([
                'kind' => 'link',
                'name' => 'Lien de référence',
                'url' => $validated['links'],
            ]);
        }

        $report->history()->create([
            'label' => 'Signalement modifié par le consommateur',
            'author' => $request->user()->name,
        ]);

        return redirect()->route('account.reports.show', $ref)
            ->with('success', "Votre signalement {$ref} a été mis à jour avec succès.");
    }

    public function destroy(Request $request, string $ref): RedirectResponse
    {
        $report = $request->user()->reports()->where('ref', $ref)->firstOrFail();
        $report->delete();

        return redirect()->route('account.reports.index')
            ->with('success', "Le signalement {$ref} a bien été supprimé.");
    }

    public function message(Request $request, string $ref): RedirectResponse
    {
        $report = $request->user()->reports()->where('ref', $ref)->firstOrFail();

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $attachments = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('evidence', 'public');
            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'size' => round($file->getSize() / 1024).' Ko',
                'url' => asset('storage/'.$path),
            ];
        }

        $report->messages()->create([
            'user_id' => $request->user()->id,
            'role' => 'reporter',
            'body' => $validated['message'],
            'attachments' => $attachments,
        ]);

        $report->history()->create([
            'label' => 'Message envoyé par le consommateur',
            'author' => $request->user()->name,
        ]);

        return redirect()->route('account.reports.show', $ref)->with('success', 'Message envoyé au modérateur.');
    }
}
