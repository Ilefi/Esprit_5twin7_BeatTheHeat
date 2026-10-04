<?php

namespace App\Providers;

use App\Models\Actor;
use App\Models\Certification;
use App\Models\Product;
use App\View\Composers\AdminNotificationsComposer;
use App\View\Composers\AdminSidebarComposer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Paginator::defaultView('vendor.pagination.nutritrace');
        Paginator::defaultSimpleView('vendor.pagination.nutritrace');

        // Short names stored in reports.reportable_type
        Relation::morphMap([
            'product' => Product::class,
            'actor' => Actor::class,
            'certification' => Certification::class,
        ]);

        View::composer(['partials.admin.sidebar', 'partials.admin.topbar'], AdminSidebarComposer::class);
        View::composer('partials.admin.topbar', AdminNotificationsComposer::class);
    }
}
