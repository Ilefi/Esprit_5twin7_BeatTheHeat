<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TraceabilityController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($code = trim((string) $request->query('code'))) {
            // Lot codes are stored upper-case (NT-2026-OLV-0412).
            $batch = Batch::where('code', mb_strtoupper($code))->first();

            return $batch
                ? redirect()->route('front.traceability.batch', $batch->code)
                : redirect()->route('front.traceability.index')->withInput()->with('error', "Aucun lot ne correspond au code « {$code} ». Vérifiez la saisie (ex. NT-2026-OLV-0412).");
        }

        return view('front.traceability.index', [
            'batches' => Batch::with(['product.impact', 'product.category', 'steps'])->orderBy('id')->get(),
        ]);
    }

    public function show(string $code): View
    {
        return view('front.traceability.show', [
            'batch' => Batch::where('code', mb_strtoupper($code))->with(['product.impact', 'product.category', 'steps.actor'])->firstOrFail(),
        ]);
    }
}
