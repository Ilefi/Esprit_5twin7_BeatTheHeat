<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Certification;
use App\Models\CertificationVerification;
use App\Models\Impact;
use App\Models\Product;
use App\Models\Report;
use App\Models\Review;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $reportStatuses = StatusBadge::options('report');
        $reportsByStatus = Report::pluck('status')->countBy();
        $grades = Impact::pluck('eco_score');
        $gradeCounts = $grades->countBy();
        $gradePoints = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1];
        $averagePoints = $grades->avg(fn ($grade) => $gradePoints[$grade]);
        $certifications = Certification::withCount('products')->get();
        $months = collect(range(11, 0))->map(fn ($m) => now()->startOfMonth()->subMonths($m));

        return view('admin.dashboard', [
            'kpis' => [
                ['icon' => 'fa-basket-shopping', 'value' => Product::count(), 'label' => 'Produits référencés', 'trend' => 8, 'good' => true, 'tone' => 'primary', 'route' => 'admin.products.index'],
                ['icon' => 'fa-boxes-stacked', 'value' => Batch::count(), 'label' => 'Lots tracés', 'trend' => 12, 'good' => true, 'tone' => 'info', 'route' => 'admin.batches.index'],
                ['icon' => 'fa-file-circle-check', 'value' => CertificationVerification::where('status', 'pending')->count(), 'label' => 'Certifications à vérifier', 'trend' => -10, 'good' => false, 'tone' => 'gold', 'route' => 'admin.certifications.verifications'],
                ['icon' => 'fa-flag', 'value' => Report::open()->count(), 'label' => 'Signalements ouverts', 'trend' => 5, 'good' => false, 'tone' => 'danger', 'route' => 'admin.reports.index'],
                ['icon' => 'fa-comment-dots', 'value' => Review::awaitingModeration()->count(), 'label' => 'Avis en attente', 'trend' => -4, 'good' => false, 'tone' => 'warning', 'route' => 'admin.reviews.index'],
                ['icon' => 'fa-leaf', 'value' => array_search((int) round($averagePoints ?? 4), $gradePoints, true) ?: 'B', 'label' => 'Éco-score moyen', 'trend' => 3, 'good' => true, 'tone' => 'primary', 'route' => 'admin.impacts.index'],
            ],
            'charts' => [
                'status' => [
                    'type' => 'doughnut',
                    'labels' => array_values($reportStatuses),
                    'datasets' => [['label' => 'Signalements', 'data' => collect(array_keys($reportStatuses))->map(fn ($s) => $reportsByStatus[$s] ?? 0)->all(), 'colors' => ['warning', 'info', 'danger', 'muted-foreground', 'primary']]],
                ],
                'activity' => [
                    'type' => 'line',
                    'labels' => $months->map(fn (Carbon $month) => ucfirst($month->translatedFormat('M')))->all(),
                    'datasets' => [
                        ['label' => 'Avis', 'data' => $this->monthlyCounts(Review::query(), $months), 'color' => 'primary'],
                        ['label' => 'Signalements', 'data' => $this->monthlyCounts(Report::query(), $months), 'color' => 'danger'],
                    ],
                ],
                'eco' => [
                    'type' => 'bar',
                    'labels' => ['A', 'B', 'C', 'D', 'E'],
                    'datasets' => [['label' => 'Produits', 'data' => collect(['A', 'B', 'C', 'D', 'E'])->map(fn ($g) => $gradeCounts[$g] ?? 0)->all(), 'colors' => ['eco-a', 'eco-b', 'eco-c', 'eco-d', 'eco-e']]],
                    'legend' => false,
                ],
                'certifications' => [
                    'type' => 'bar',
                    'horizontal' => true,
                    'labels' => $certifications->pluck('short_name')->all(),
                    'datasets' => [['label' => 'Produits certifiés', 'data' => $certifications->pluck('products_count')->all(), 'colors' => ['primary', 'earth', 'gold', 'info', 'success', 'muted-foreground']]],
                    'legend' => false,
                ],
            ],
            'latestReports' => Report::withTarget()->latest()->take(5)->get(),
            'pendingReviews' => Review::awaitingModeration()->with(['product', 'user'])->latest()->take(5)->get(),
            'alerts' => Report::withTarget()
                ->whereIn('status', ['pending', 'in_review', 'confirmed'])
                ->where(fn (Builder $query) => $query
                    ->where('priority', 'critical')
                    ->orWhere(fn (Builder $query) => $query->where('type', 'greenwashing')->where('priority', 'high')))
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * Number of records created in each of the given months (DB-agnostic: grouped in PHP).
     *
     * @param  Collection<int, Carbon>  $months
     * @return list<int>
     */
    private function monthlyCounts(Builder $query, $months): array
    {
        $counts = $query->where('created_at', '>=', $months->first())
            ->pluck('created_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return $months->map(fn (Carbon $month) => $counts[$month->format('Y-m')] ?? 0)->all();
    }
}
