<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObservatoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        // TODO(Gestion 4): replace DemoData with Report::public()->filter($request)->paginate()
        $reports = DemoData::reports();
        $types = StatusBadge::options('report_type');

        $public = $reports->whereIn('status', ['confirmed', 'resolved']);
        if (array_key_exists($type = (string) $request->query('type'), $types)) {
            $public = $public->where('type', $type);
        }
        if (in_array($status = (string) $request->query('issue'), ['confirmed', 'resolved'], true)) {
            $public = $public->where('status', $status);
        }

        $byType = $reports->countBy('type');
        $closed = $reports->whereIn('status', ['confirmed', 'rejected', 'resolved']);

        return view('front.observatory', [
            'publicReports' => DemoData::paginate($public->sortByDesc('created_at')->values(), 6),
            'types' => $types,
            'kpis' => [
                ['icon' => 'fa-flag', 'value' => $reports->count(), 'label' => 'signalements reçus', 'tone' => 'info'],
                ['icon' => 'fa-circle-exclamation', 'value' => $reports->where('status', 'confirmed')->count(), 'label' => 'allégations jugées fondées', 'tone' => 'danger'],
                ['icon' => 'fa-check-double', 'value' => $closed->count(), 'label' => 'dossiers clôturés', 'tone' => 'primary'],
                ['icon' => 'fa-stopwatch', 'value' => '6 j', 'label' => 'délai moyen de traitement', 'tone' => 'gold'],
            ],
            'chart' => [
                'type' => 'bar',
                'horizontal' => true,
                'labels' => array_values($types),
                'datasets' => [[
                    'label' => 'Signalements',
                    'data' => collect(array_keys($types))->map(fn ($t) => $byType[$t] ?? 0)->all(),
                    'colors' => ['danger', 'gold', 'info', 'warning', 'earth', 'muted-foreground'],
                ]],
                'legend' => false,
            ],
        ]);
    }
}
