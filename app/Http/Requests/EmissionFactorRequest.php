<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmissionFactorRequest extends FormRequest
{
    public const CATEGORIES = [
        'Transport' => 'Transport',
        'Emballage' => 'Emballage',
        'Énergie' => 'Énergie',
        'Eau' => 'Eau',
        'Intrants' => 'Intrants',
        'Transformation' => 'Transformation',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $factorId = $this->route('facteurs_emission') ?? $this->route('factor');
        if (is_object($factorId)) {
            $factorId = $factorId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('emission_factors', 'name')->ignore($factorId),
            ],
            'category' => [
                'required',
                'string',
                'max:100',
            ],
            'unit' => [
                'required',
                'string',
                'max:50',
            ],
            'value' => [
                'required',
                'numeric',
                'min:0.0001',
                'max:9999999.999',
            ],
            'source' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du facteur d’émission est obligatoire.',
            'name.unique' => 'Ce facteur d’émission existe déjà.',
            'name.min' => 'Le nom doit comporter au moins :min caractères.',
            'category.required' => 'La catégorie est obligatoire.',
            'unit.required' => 'L’unité de mesure est obligatoire (ex. kg CO₂e / kg).',
            'value.required' => 'La valeur numérique est obligatoire.',
            'value.numeric' => 'La valeur doit être un nombre valide.',
            'value.min' => 'La valeur doit être strictement positive.',
            'source.required' => 'La source de la donnée est obligatoire (ex. ADEME, Base Carbone).',
        ];
    }
}
