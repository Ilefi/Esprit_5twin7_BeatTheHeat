<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Veuillez attribuer une note.',
            'title.required' => 'Le titre est obligatoire.',
            'body.required' => 'Le commentaire est obligatoire.',
            'body.min' => 'Le commentaire doit comporter au moins :min caractères.',
        ];
    }
}
