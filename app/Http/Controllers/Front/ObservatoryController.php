<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObservatoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $types = StatusBadge::options('report_type');
        $type = (string) $request->query('type');
        $issue = (string) $request->query('issue');

        $publicReports = Report::whereIn('status', ['confirmed', 'resolved'])->withTarget()
            ->when(array_key_exists($type, $types), fn (Builder $query) => $query->where('type', $type))
            ->when(in_array($issue, ['confirmed', 'resolved'], true), fn (Builder $query) => $query->where('status', $issue))
            ->latest();

        $byType = Report::pluck('type')->countBy();

        return view('front.observatory', [
            'publicReports' => $publicReports->paginate(6)->withQueryString(),
            'types' => $types,
            'kpis' => [
                ['icon' => 'fa-flag', 'value' => Report::count(), 'label' => 'signalements reçus', 'tone' => 'info'],
                ['icon' => 'fa-circle-exclamation', 'value' => Report::where('status', 'confirmed')->count(), 'label' => 'allégations jugées fondées', 'tone' => 'danger'],
                ['icon' => 'fa-check-double', 'value' => Report::whereIn('status', Report::CLOSED_STATUSES)->count(), 'label' => 'dossiers clôturés', 'tone' => 'primary'],
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
