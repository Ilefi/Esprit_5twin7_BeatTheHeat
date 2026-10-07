<?php

namespace App\Http\Requests;

use App\View\Components\StatusBadge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin create & update of a product (the image is optional on update: the current one is kept).
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150', Rule::unique('products')->ignore($this->route('product'))],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'producer_id' => ['required', 'integer', Rule::exists('actors', 'id')->where('type', 'producer')],
            'region' => ['required', 'string', 'max:80'],
            'format' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.1', 'max:10000'],
            'status' => ['required', Rule::in(array_keys(StatusBadge::options('product')))],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'composition' => ['nullable', 'string', 'max:1000'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['integer', 'distinct', Rule::exists('certifications', 'id')],
            'image' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'image', 'mimes:jpg,jpeg,png,webp', 'max:4096',
                Rule::dimensions()->minWidth(400)->minHeight(300)->maxWidth(4000)->maxHeight(4000),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Un produit porte déjà ce nom.',
            'producer_id.exists' => 'Le producteur sélectionné n\'existe pas ou n\'est pas un producteur.',
            'price.decimal' => 'Le prix doit comporter au plus 2 décimales.',
            'certifications.*.exists' => 'Une des certifications sélectionnées n\'existe pas.',
            'image.required' => 'Une photo du produit est obligatoire.',
            'image.mimes' => 'L\'image doit être au format JPG, PNG ou WebP.',
            'image.max' => 'L\'image ne doit pas dépasser 4 Mo.',
            'image.dimensions' => 'L\'image doit mesurer entre 400 × 300 et 4000 × 4000 pixels.',
        ];
    }
}
