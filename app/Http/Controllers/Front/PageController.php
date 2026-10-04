<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public const CONTACT_SUBJECTS = [
        'question' => 'Question générale',
        'producer' => 'Je suis producteur / acteur',
        'press' => 'Presse & partenariats',
        'bug' => 'Signaler un problème technique',
    ];

    public function about(): View
    {
        return view('front.pages.about');
    }

    public function how(): View
    {
        return view('front.pages.how', [
            'exampleBatch' => Batch::orderBy('id')->first(),
        ]);
    }

    public function faq(): View
    {
        return view('front.pages.faq', [
            'faqs' => Faq::orderBy('position')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('front.pages.contact', [
            'subjects' => self::CONTACT_SUBJECTS,
        ]);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'in:'.implode(',', array_keys(self::CONTACT_SUBJECTS))],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // TODO: send the message (Mail::to(...)) once mail is configured.
        return redirect()->route('front.contact')->with('success', 'Merci ! Votre message a bien été envoyé, nous vous répondrons sous 48 h.');
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'newsletter_email' => ['required', 'email', 'max:150'],
        ], [], ['newsletter_email' => 'adresse e-mail']);

        return back()->with('success', 'Inscription confirmée : vous recevrez la prochaine lettre NutriTrace.');
    }
}
