<?php

namespace App\Http\Requests;

use App\View\Components\StatusBadge;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(StatusBadge::options('report_type')))],
            'target_type' => ['required', 'string', 'in:product,actor,certification'],
            'target_id' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'min:30', 'max:3000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'links' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Veuillez sélectionner le type d\'anomalie constatée.',
            'description.required' => 'Une description détaillée des faits est obligatoire.',
            'description.min' => 'La description doit comporter au moins :min caractères.',
            'evidence.*.mimes' => 'Les pièces jointes doivent être au format PDF, JPG ou PNG.',
            'evidence.*.max' => 'Chaque fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}
