<?php

namespace App\View\Composers;

use App\Support\DemoData;
use Illuminate\View\View;

class AdminSidebarComposer
{
    public function compose(View $view): void
    {
        // TODO(Gestion 4): replace DemoData with Report::pending()->count() / Review::pending()->count()
        $view->with([
            'pendingReportsCount' => DemoData::reports()->whereIn('status', ['pending', 'in_review'])->count(),
            'pendingReviewsCount' => DemoData::reviews()->whereIn('status', ['pending', 'flagged'])->count(),
            'pendingVerificationsCount' => DemoData::verifications()->where('status', 'pending')->count(),
        ]);
    }
}
