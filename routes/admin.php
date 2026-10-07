<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Back office — /admin, middleware auth + admin, route names admin.*
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    // Module 1 — Produits & Certifications
    Route::resource('produits', Admin\ProductController::class)
        ->names('products')->parameters(['produits' => 'product'])->whereNumber('product');

    Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update')->whereNumber('category');
    Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy')->whereNumber('category');

    Route::get('certifications/verifications', [Admin\CertificationVerificationController::class, 'index'])->name('certifications.verifications');
    Route::post('certifications/verifications/{verification}/approuver', [Admin\CertificationVerificationController::class, 'approve'])->name('certifications.verifications.approve')->whereNumber('verification');
    Route::post('certifications/verifications/{verification}/refuser', [Admin\CertificationVerificationController::class, 'reject'])->name('certifications.verifications.reject')->whereNumber('verification');
    Route::resource('certifications', Admin\CertificationController::class)->except('show')->whereNumber('certification');

    // Module 2 — Traçabilité
    Route::resource('acteurs', Admin\ActorController::class)
        ->except('show')->names('actors')->parameters(['acteurs' => 'actor'])->whereNumber('actor');
    Route::resource('lots', Admin\BatchController::class)
        ->names('batches')->parameters(['lots' => 'batch'])->whereNumber('batch');
    Route::post('lots/{batch}/etapes', [Admin\BatchStepController::class, 'store'])->name('batches.steps.store')->whereNumber('batch');
    Route::put('lots/{batch}/etapes/{step}', [Admin\BatchStepController::class, 'update'])->name('batches.steps.update')->whereNumber(['batch', 'step']);
    Route::delete('lots/{batch}/etapes/{step}', [Admin\BatchStepController::class, 'destroy'])->name('batches.steps.destroy')->whereNumber(['batch', 'step']);
    Route::post('lots/{batch}/etapes/{step}/deplacer', [Admin\BatchStepController::class, 'move'])->name('batches.steps.move')->whereNumber(['batch', 'step']);

    // Module 3 — Empreinte environnementale
    Route::get('empreinte/facteurs', [Admin\ImpactController::class, 'factors'])->name('impacts.factors');
    Route::resource('empreinte', Admin\ImpactController::class)
        ->except('show')->names('impacts')->parameters(['empreinte' => 'impact'])->whereNumber('impact');

    // Module 4 — Signalements & Avis
    Route::get('avis', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('avis/lot', [Admin\ReviewController::class, 'bulk'])->name('reviews.bulk');
    Route::get('avis/{review}', [Admin\ReviewController::class, 'show'])->name('reviews.show')->whereNumber('review');
    Route::patch('avis/{review}/moderation', [Admin\ReviewController::class, 'moderate'])->name('reviews.moderate')->whereNumber('review');
    Route::delete('avis/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy')->whereNumber('review');

    Route::get('signalements', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('signalements/{ref}', [Admin\ReportController::class, 'show'])->name('reports.show');
    Route::patch('signalements/{ref}', [Admin\ReportController::class, 'update'])->name('reports.update');
    Route::patch('signalements/{ref}/statut', [Admin\ReportController::class, 'status'])->name('reports.status');
    Route::post('signalements/{ref}/notes', [Admin\ReportController::class, 'note'])->name('reports.notes.store');
    Route::post('signalements/{ref}/reponse', [Admin\ReportController::class, 'reply'])->name('reports.reply');
    Route::post('signalements/{ref}/ai-analyse', [Admin\ReportController::class, 'aiAnalyze'])->name('reports.ai.analyze');

    // Utilisateurs
    Route::get('utilisateurs', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('utilisateurs/{user}/modifier', [Admin\UserController::class, 'edit'])->name('users.edit')->whereNumber('user');
    Route::put('utilisateurs/{user}', [Admin\UserController::class, 'update'])->name('users.update')->whereNumber('user');
});
