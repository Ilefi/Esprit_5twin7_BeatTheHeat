<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Front;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Front office — French URLs, English route names (front.*, account.*)
|--------------------------------------------------------------------------
*/

Route::name('front.')->group(function () {
    Route::get('/', Front\HomeController::class)->name('home');

    // Static pages
    Route::controller(Front\PageController::class)->group(function () {
        Route::get('/a-propos', 'about')->name('about');
        Route::get('/comment-ca-marche', 'how')->name('how');
        Route::get('/faq', 'faq')->name('faq');
        Route::get('/contact', 'contact')->name('contact');
        Route::post('/contact', 'sendContact')->name('contact.send');
        Route::post('/newsletter', 'subscribe')->name('newsletter');
    });

    // Module 1 — Produits & Certifications
    Route::get('/produits', [Front\ProductController::class, 'index'])->name('products.index');
    Route::get('/produits/{slug}', [Front\ProductController::class, 'show'])->name('products.show');
    Route::get('/certifications', [Front\CertificationController::class, 'index'])->name('certifications.index');
    Route::get('/certifications/{slug}', [Front\CertificationController::class, 'show'])->name('certifications.show');

    // Module 2 — Chaîne de traçabilité
    Route::get('/tracabilite', [Front\TraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/tracabilite/lots/{code}', [Front\TraceabilityController::class, 'show'])->name('traceability.batch');
    Route::get('/acteurs', [Front\ActorController::class, 'index'])->name('actors.index');
    Route::get('/acteurs/{slug}', [Front\ActorController::class, 'show'])->name('actors.show');

    // Module 3 — Empreinte environnementale
    Route::get('/empreinte', [Front\ImpactController::class, 'index'])->name('impact.index');
    Route::get('/empreinte/comparer', [Front\ImpactController::class, 'compare'])->name('impact.compare');

    // Module 4 — Signalements & Avis
    Route::get('/observatoire', Front\ObservatoryController::class)->name('observatory');

    Route::middleware('auth')->group(function () {
        Route::get('/signalements/nouveau', [Front\ReportController::class, 'create'])->name('reports.create');
        Route::post('/signalements', [Front\ReportController::class, 'store'])->name('reports.store');
        Route::post('/produits/{slug}/avis', [Front\ReviewController::class, 'store'])->name('reviews.store');
        Route::post('/avis/{review}/utile', [Front\ReviewController::class, 'helpful'])->name('reviews.helpful')->whereNumber('review');
    });
});

/*
| Consumer space (layouts.account). The Breeze profile keeps its /profile URL.
*/
Route::middleware('auth')->prefix('mon-espace')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('dashboard');

    Route::get('/avis', [Account\ReviewController::class, 'index'])->name('reviews.index');
    Route::put('/avis/{review}', [Account\ReviewController::class, 'update'])->name('reviews.update')->whereNumber('review');
    Route::delete('/avis/{review}', [Account\ReviewController::class, 'destroy'])->name('reviews.destroy')->whereNumber('review');

    Route::get('/signalements', [Account\ReportController::class, 'index'])->name('reports.index');
    Route::get('/signalements/{ref}/modifier', [Account\ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/signalements/{ref}', [Account\ReportController::class, 'update'])->name('reports.update');
    Route::delete('/signalements/{ref}', [Account\ReportController::class, 'destroy'])->name('reports.destroy');
    Route::get('/signalements/{ref}', [Account\ReportController::class, 'show'])->name('reports.show');
    Route::post('/signalements/{ref}/messages', [Account\ReportController::class, 'message'])->name('reports.message');

    Route::redirect('/profil', '/profile')->name('profile');
});
