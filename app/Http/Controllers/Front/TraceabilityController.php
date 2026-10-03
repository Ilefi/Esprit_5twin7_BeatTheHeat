<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TraceabilityController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // TODO(Gestion 2): replace DemoData with Batch::where('code', $code)->first()
        if ($code = trim((string) $request->query('code'))) {
            $batch = DemoData::batches()->first(fn ($b) => strcasecmp($b->code, $code) === 0);

            return $batch
                ? redirect()->route('front.traceability.batch', $batch->code)
                : redirect()->route('front.traceability.index')->withInput()->with('error', "Aucun lot ne correspond au code « {$code} ». Vérifiez la saisie (ex. NT-2026-OLV-0412).");
        }

        return view('front.traceability.index', [
            'batches' => DemoData::batches(),
        ]);
    }

    public function show(string $code): View
    {
        // TODO(Gestion 2): replace DemoData with Batch::where('code', $code)->with('steps.actor', 'product')->firstOrFail()
        return view('front.traceability.show', [
            'batch' => DemoData::batch($code),
        ]);
    }
}
