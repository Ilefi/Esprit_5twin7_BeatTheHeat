<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // TODO(Gestion 4): replace DemoData with $request->user()->reviews() / ->reports()
        $reviews = DemoData::accountReviews();
        $reports = DemoData::accountReports();

        $activity = $reviews->map(fn ($r) => (object) [
            'icon' => 'fa-star', 'tone' => 'gold', 'title' => 'Avis publié sur '.$r->product->name, 'at' => $r->created_at,
            'url' => route('front.products.show', $r->product->slug).'#avis',
        ])->merge($reports->map(fn ($r) => (object) [
            'icon' => 'fa-flag', 'tone' => 'danger', 'title' => 'Signalement '.$r->ref.' — '.$r->target->name, 'at' => $r->updated_at,
            'url' => route('account.reports.show', $r->ref),
        ]))->sortByDesc('at')->take(6)->values();

        return view('account.dashboard', [
            'stats' => [
                ['icon' => 'fa-star', 'value' => $reviews->count(), 'label' => 'avis rédigés', 'tone' => 'gold'],
                ['icon' => 'fa-thumbs-up', 'value' => $reviews->sum('helpful_count'), 'label' => 'votes « utile » reçus', 'tone' => 'primary'],
                ['icon' => 'fa-flag', 'value' => $reports->count(), 'label' => 'signalements envoyés', 'tone' => 'danger'],
                ['icon' => 'fa-hourglass-half', 'value' => $reports->whereIn('status', ['pending', 'in_review'])->count(), 'label' => 'dossiers en cours', 'tone' => 'info'],
            ],
            'activity' => $activity,
            'openReports' => $reports->whereIn('status', ['pending', 'in_review'])->values(),
            'suggestions' => DemoData::products()->where('status', 'published')->whereIn('eco_score', ['A', 'B'])->take(3)->values(),
        ]);
    }
}
