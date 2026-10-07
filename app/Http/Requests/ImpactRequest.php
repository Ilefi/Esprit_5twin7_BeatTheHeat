<?php

namespace App\Http\Requests;

use App\Support\EcoScore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Admin create & update of a product footprint. The product is chosen on create only (one footprint per product).
 */
class ImpactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $rules = [
            'co2_per_kg' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'water_per_kg' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:50000'],
            'distance_km' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:20000'],
            'packaging' => ['required', Rule::in(array_keys(EcoScore::PACKAGING_LABELS))],
            'seasonal' => ['required', 'boolean'],
            'methodology' => ['required', Rule::in(array_keys(EcoScore::METHODOLOGIES))],
            'source' => ['nullable', 'string', 'max:255'],
            'breakdown' => ['required', 'array:'.implode(',', EcoScore::BREAKDOWN_STAGES)],
        ];

        foreach (EcoScore::BREAKDOWN_STAGES as $stage) {
            $rules["breakdown.{$stage}"] = ['required', 'integer', 'between:0,100'];
        }

        if ($this->isMethod('post')) {
            $rules['product_id'] = ['required', 'integer', Rule::exists('products', 'id'), Rule::unique('impacts', 'product_id')];
        }

        return $rules;
    }

    /** The breakdown shares must add up to exactly 100 %. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['breakdown', 'breakdown.*'])) {
                    return;
                }

                $total = array_sum($this->input('breakdown'));
                if ($total !== 100) {
                    $validator->errors()->add('breakdown', "La répartition des émissions doit totaliser 100 % (actuellement {$total} %).");
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique' => 'Ce produit a déjà une empreinte : modifiez-la plutôt que d\'en créer une nouvelle.',
            'co2_per_kg.max' => 'Les émissions doivent rester sous :max kg CO₂e par kg (valeur incohérente).',
            'water_per_kg.max' => 'La consommation d\'eau doit rester sous :max L par kg (valeur incohérente).',
            'distance_km.max' => 'La distance doit rester sous :max km (valeur incohérente).',
            'breakdown.*.between' => 'Chaque part de la répartition doit être comprise entre :min et :max %.',
        ];
    }

    public function attributes(): array
    {
        return collect(EcoScore::BREAKDOWN_STAGES)
            ->mapWithKeys(fn (string $stage) => ["breakdown.{$stage}" => 'part « '.$stage.' »'])
            ->all();
    }

    /** Validated data, with the breakdown in the canonical stage order. */
    public function impactData(): array
    {
        $data = $this->safe()->except('breakdown');
        $data['breakdown'] = collect(EcoScore::BREAKDOWN_STAGES)
            ->mapWithKeys(fn (string $stage) => [$stage => (int) $this->validated("breakdown.{$stage}")])
            ->all();

        return $data;
    }
}
