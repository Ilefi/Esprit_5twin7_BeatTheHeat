<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Actor;
use App\Models\Certification;
use App\Models\Product;
use App\Models\Report;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Which wizard step owns which field — used to reopen the right step after a validation error. */
    private const STEP_FIELDS = [
        1 => ['type'],
        2 => ['target_type', 'target_id'],
        3 => ['description', 'evidence', 'links'],
        4 => ['consent'],
    ];

    private const TYPE_HINTS = [
        'greenwashing' => 'Allégation environnementale exagérée ou non prouvée.',
        'dubious_certification' => 'Label affiché sans certificat valide ou détourné.',
        'traceability_error' => 'Dates, lieux ou acteurs incohérents dans la chaîne.',
        'misleading_footprint' => 'Chiffres d\'empreinte incomplets ou trompeurs.',
        'health_quality' => 'Défaut, contamination ou problème d\'étiquetage.',
        'other' => 'Toute autre information erronée sur la plateforme.',
    ];

    public function create(Request $request): View
    {
        $targets = [
            'product' => Product::published()->with('producer')->orderBy('id')->get()->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'subtitle' => $p->producer->name]),
            'actor' => Actor::orderBy('id')->get()->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'subtitle' => $a->city]),
            'certification' => Certification::orderBy('id')->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'subtitle' => $c->issuer]),
        ];

        $targetType = old('target_type', $request->query('cible', 'product'));
        $targetType = array_key_exists($targetType, $targets) ? $targetType : 'product';

        return view('front.reports.create', [
            'types' => collect(StatusBadge::options('report_type'))->map(fn($label, $value) => [
                'value' => $value,
                'label' => $label,
                'icon' => StatusBadge::iconFor('report_type', $value),
                'hint' => self::TYPE_HINTS[$value],
            ])->values(),
            'targets' => $targets,
            'prefill' => [
                'type' => old('type', $request->query('type', '')),
                'targetType' => $targetType,
                'targetId' => (string) old('target_id', $request->query('id', '')),
                'description' => old('description', ''),
                'links' => old('links', ''),
                'step' => $this->stepWithErrors($request->session()->get('errors')),
            ],
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $report = Report::create([
            'type' => $validated['type'],
            'reportable_type' => $validated['target_type'],
            'reportable_id' => $validated['target_id'],
            'title' => 'Signalement ' . StatusBadge::labelFor('report_type', $validated['type']),
            'description' => $validated['description'],
            'status' => 'pending',
            'priority' => 'medium',
            'reporter_id' => $request->user()->id,
        ]);

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('evidence', 'public');
                $report->evidence()->create([
                    'kind' => 'file',
                    'name' => $file->getClientOriginalName(),
                    'size' => round($file->getSize() / 1024) . ' Ko',
                    'url' => asset('storage/' . $path),
                ]);
            }
        }

        if (!empty($validated['links'])) {
            $report->evidence()->create([
                'kind' => 'link',
                'name' => 'Lien de référence',
                'url' => $validated['links'],
            ]);
        }

        $report->history()->create([
            'label' => 'Signalement déposé par le consommateur',
            'author' => $request->user()->name,
        ]);

        return redirect()->route('account.reports.index')
            ->with('success', "Votre signalement {$report->ref} a bien été enregistré. Vous serez notifié à chaque étape de son traitement.");
    }

    private function stepWithErrors(?ViewErrorBag $errors): int
    {
        if (!$errors || $errors->isEmpty()) {
            return 1;
        }

        foreach (self::STEP_FIELDS as $step => $fields) {
            foreach ($fields as $field) {
                if ($errors->has($field) || $errors->has($field . '.*')) {
                    return $step;
                }
            }
        }

        return 1;
    }
}
