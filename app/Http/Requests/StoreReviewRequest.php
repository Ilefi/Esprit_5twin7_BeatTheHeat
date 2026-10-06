<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    protected $errorBag = 'review';

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'quality_rating' => ['required', 'integer', 'between:1,5'],
            'transparency_rating' => ['required', 'integer', 'between:1,5'],
            'value_rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Veuillez attribuer une note globale.',
            'quality_rating.required' => 'Veuillez évaluer la qualité perçue du produit.',
            'transparency_rating.required' => 'Veuillez évaluer la transparence des informations.',
            'value_rating.required' => 'Veuillez évaluer le rapport qualité/prix.',
            'title.required' => 'Le titre de votre avis est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'body.required' => 'Le commentaire est obligatoire.',
            'body.min' => 'Votre commentaire doit contenir au moins :min caractères pour aider les autres consommateurs.',
            'body.max' => 'Votre commentaire ne peut pas dépasser :max caractères.',
        ];
    }
}
