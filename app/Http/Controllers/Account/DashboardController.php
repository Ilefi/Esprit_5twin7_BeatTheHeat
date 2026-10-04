<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $reviews = $request->user()->reviews()->with('product')->get();
        $reports = $request->user()->reports()->withTarget()->get();

        $activity = $reviews->map(fn ($r) => (object) [
            'icon' => 'fa-star', 'tone' => 'gold', 'title' => 'Avis publié sur '.$r->product->name, 'at' => $r->created_at,
            'url' => route('front.products.show', $r->product->slug).'#avis',
        ])->merge($reports->map(fn ($r) => (object) [
            'icon' => 'fa-flag', 'tone' => 'danger', 'title' => 'Signalement '.$r->ref.' — '.$r->target->name, 'at' => $r->updated_at,
            'url' => route('account.reports.show', $r->ref),
        ]))->sortByDesc('at')->take(6)->values();

        $openReports = $reports->whereIn('status', Report::OPEN_STATUSES)->values();

        return view('account.dashboard', [
            'stats' => [
                ['icon' => 'fa-star', 'value' => $reviews->count(), 'label' => 'avis rédigés', 'tone' => 'gold'],
                ['icon' => 'fa-thumbs-up', 'value' => $reviews->sum('helpful_count'), 'label' => 'votes « utile » reçus', 'tone' => 'primary'],
                ['icon' => 'fa-flag', 'value' => $reports->count(), 'label' => 'signalements envoyés', 'tone' => 'danger'],
                ['icon' => 'fa-hourglass-half', 'value' => $openReports->count(), 'label' => 'dossiers en cours', 'tone' => 'info'],
            ],
            'activity' => $activity,
            'openReports' => $openReports,
            'suggestions' => Product::published()->forCards()
                ->whereHas('impact', fn (Builder $query) => $query->whereIn('eco_score', ['A', 'B']))
                ->orderBy('id')
                ->take(3)
                ->get(),
        ]);
    }
}
