<?php

namespace App\View\Composers;

use App\Models\CertificationVerification;
use App\Models\Report;
use App\Models\Review;
use Illuminate\View\View;

class AdminSidebarComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'pendingReportsCount' => Report::open()->count(),
            'pendingReviewsCount' => Review::awaitingModeration()->count(),
            'pendingVerificationsCount' => CertificationVerification::where('status', 'pending')->count(),
        ]);
    }
}
