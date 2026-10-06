<?php

namespace App\Http\Requests;

use App\View\Components\StatusBadge;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Veuillez sélectionner le type d\'anomalie constatée.',
            'type.in' => 'Le type d\'anomalie sélectionné n\'est pas valide.',
            'target_type.required' => 'Veuillez indiquer la catégorie de l\'élément concerné.',
            'target_id.required' => 'Veuillez sélectionner l\'élément concerné.',
            'description.required' => 'Une description détaillée des faits est obligatoire.',
            'description.min' => 'La description doit comporter au moins :min caractères pour permettre une analyse précise.',
            'description.max' => 'La description ne peut pas dépasser :max caractères.',
            'evidence.max' => 'Vous ne pouvez pas joindre plus de 5 fichiers.',
            'evidence.*.mimes' => 'Les pièces jointes doivent être au format PDF, JPG ou PNG.',
            'evidence.*.max' => 'Chaque pièce jointe ne doit pas dépasser 5 Mo.',
            'consent.accepted' => 'Vous devez certifier sur l\'honneur l\'exactitude de votre signalement.',
        ];
    }
}
