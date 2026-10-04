<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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
            'product' => Product::published()->with('producer')->orderBy('id')->get()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'subtitle' => $p->producer->name]),
            'actor' => Actor::orderBy('id')->get()->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'subtitle' => $a->city]),
            'certification' => Certification::orderBy('id')->get()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'subtitle' => $c->issuer]),
        ];

        $targetType = old('target_type', $request->query('cible', 'product'));
        $targetType = array_key_exists($targetType, $targets) ? $targetType : 'product';

        return view('front.reports.create', [
            'types' => collect(StatusBadge::options('report_type'))->map(fn ($label, $value) => [
                'value' => $value, 'label' => $label, 'icon' => StatusBadge::iconFor('report_type', $value), 'hint' => self::TYPE_HINTS[$value],
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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('report_type')))],
            'target_type' => ['required', 'in:product,actor,certification'],
            'target_id' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'min:30', 'max:3000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'links' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
        ]);

        // TODO(Gestion 4): Report::create([...]) + store evidence files
        $ref = Report::nextRef();

        return redirect()->route('account.reports.index')
            ->with('success', "Votre signalement {$ref} a bien été enregistré. Vous serez notifié à chaque étape de son traitement.");
    }

    private function stepWithErrors(?ViewErrorBag $errors): int
    {
        if (! $errors || $errors->isEmpty()) {
            return 1;
        }

        foreach (self::STEP_FIELDS as $step => $fields) {
            foreach ($fields as $field) {
                if ($errors->has($field) || $errors->has($field.'.*')) {
                    return $step;
                }
            }
        }

        return 1;
    }
}
