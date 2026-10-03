<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // TODO(Gestion 1-4): replace DemoData with aggregate queries
        $products = DemoData::products();
        $reports = DemoData::reports();
        $reviews = DemoData::reviews();
        $stats = DemoData::monthlyStats();
        $reportStatuses = StatusBadge::options('report');
        $gradePoints = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1];
        $averagePoints = $products->avg(fn ($p) => $gradePoints[$p->eco_score]);

        return view('admin.dashboard', [
            'kpis' => [
                ['icon' => 'fa-basket-shopping', 'value' => $products->count(), 'label' => 'Produits référencés', 'trend' => 8, 'good' => true, 'tone' => 'primary', 'route' => 'admin.products.index'],
                ['icon' => 'fa-boxes-stacked', 'value' => DemoData::batches()->count(), 'label' => 'Lots tracés', 'trend' => 12, 'good' => true, 'tone' => 'info', 'route' => 'admin.batches.index'],
                ['icon' => 'fa-file-circle-check', 'value' => DemoData::verifications()->where('status', 'pending')->count(), 'label' => 'Certifications à vérifier', 'trend' => -10, 'good' => false, 'tone' => 'gold', 'route' => 'admin.certifications.verifications'],
                ['icon' => 'fa-flag', 'value' => $reports->whereIn('status', ['pending', 'in_review'])->count(), 'label' => 'Signalements ouverts', 'trend' => 5, 'good' => false, 'tone' => 'danger', 'route' => 'admin.reports.index'],
                ['icon' => 'fa-comment-dots', 'value' => $reviews->whereIn('status', ['pending', 'flagged'])->count(), 'label' => 'Avis en attente', 'trend' => -4, 'good' => false, 'tone' => 'warning', 'route' => 'admin.reviews.index'],
                ['icon' => 'fa-leaf', 'value' => array_search((int) round($averagePoints), $gradePoints, true) ?: 'B', 'label' => 'Éco-score moyen', 'trend' => 3, 'good' => true, 'tone' => 'primary', 'route' => 'admin.impacts.index'],
            ],
            'charts' => [
                'status' => [
                    'type' => 'doughnut',
                    'labels' => array_values($reportStatuses),
                    'datasets' => [['label' => 'Signalements', 'data' => collect(array_keys($reportStatuses))->map(fn ($s) => $reports->where('status', $s)->count())->all(), 'colors' => ['warning', 'info', 'danger', 'muted-foreground', 'primary']]],
                ],
                'activity' => [
                    'type' => 'line',
                    'labels' => $stats->labels,
                    'datasets' => [
                        ['label' => 'Avis', 'data' => $stats->reviews, 'color' => 'primary'],
                        ['label' => 'Signalements', 'data' => $stats->reports, 'color' => 'danger'],
                    ],
                ],
                'eco' => [
                    'type' => 'bar',
                    'labels' => ['A', 'B', 'C', 'D', 'E'],
                    'datasets' => [['label' => 'Produits', 'data' => collect(['A', 'B', 'C', 'D', 'E'])->map(fn ($g) => $products->where('eco_score', $g)->count())->all(), 'colors' => ['eco-a', 'eco-b', 'eco-c', 'eco-d', 'eco-e']]],
                    'legend' => false,
                ],
                'certifications' => [
                    'type' => 'bar',
                    'horizontal' => true,
                    'labels' => DemoData::certifications()->pluck('short_name')->all(),
                    'datasets' => [['label' => 'Produits certifiés', 'data' => DemoData::certifications()->pluck('products_count')->all(), 'colors' => ['primary', 'earth', 'gold', 'info', 'success', 'muted-foreground']]],
                    'legend' => false,
                ],
            ],
            'latestReports' => $reports->sortByDesc('created_at')->take(5)->values(),
            'pendingReviews' => $reviews->whereIn('status', ['pending', 'flagged'])->sortByDesc('created_at')->take(5)->values(),
            'alerts' => $reports->where('type', 'greenwashing')->whereIn('priority', ['high', 'critical'])->merge(
                $reports->where('priority', 'critical')
            )->unique('id')->whereIn('status', ['pending', 'in_review', 'confirmed'])->values(),
        ]);
    }
}
