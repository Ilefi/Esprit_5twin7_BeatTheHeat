<?php

namespace App\View\Composers;

use App\Models\Batch;
use App\Models\CertificationVerification;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Topbar notifications: what currently needs a moderator's attention, newest first.
 */
class AdminNotificationsComposer
{
    public function compose(View $view): void
    {
        $notifications = collect();

        if ($report = Report::open()->where('priority', 'critical')->withTarget()->latest()->first()) {
            $notifications->push($this->item('fa-flag', 'danger', 'Nouveau signalement critique', $report->ref.' · '.$report->target->name, $report->created_at));
        }

        if ($pending = Review::awaitingModeration()->count()) {
            $flagged = Review::where('status', 'flagged')->count();
            $notifications->push($this->item('fa-comment', 'warning', $pending.' avis en attente de modération', 'Dont '.$flagged.' signalés par la communauté', Review::awaitingModeration()->max('created_at')));
        }

        if ($verification = CertificationVerification::where('status', 'pending')->with('actor', 'certification')->latest('submitted_at')->first()) {
            $notifications->push($this->item('fa-award', 'gold', 'Certificat à vérifier', $verification->actor->name.' · '.$verification->certification->short_name, $verification->submitted_at));
        }

        if ($batch = Batch::where('status', 'in_transit')->with('product')->latest('updated_at')->first()) {
            $notifications->push($this->item('fa-truck', 'info', 'Lot en transit', $batch->code.' · '.$batch->product->name, $batch->updated_at));
        }

        $view->with('notifications', $notifications->sortByDesc('at')->values());
    }

    private function item(string $icon, string $tone, string $title, string $body, mixed $at): object
    {
        return (object) ['icon' => $icon, 'tone' => $tone, 'title' => $title, 'body' => $body, 'at' => Carbon::parse($at)];
    }
}
