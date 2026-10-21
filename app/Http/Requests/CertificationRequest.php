<?php

namespace App\Http\Requests;

use App\Http\Controllers\Admin\CertificationController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Admin create & update of a certification. Criteria, guarantees and limits are typed one per line.
 */
class CertificationRequest extends FormRequest
{
    private const LIST_FIELDS = ['criteria', 'guarantees', 'limits'];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $unique = Rule::unique('certifications')->ignore($this->route('certification'));

        return [
            'name' => ['required', 'string', 'min:3', 'max:120', $unique],
            'short_name' => ['required', 'string', 'max:30', $unique],
            'type' => ['required', Rule::in(array_keys(CertificationController::TYPES))],
            'issuer' => ['required', 'string', 'max:150'],
            // A new certification must still be valid; an existing one may keep its past date.
            'expires_at' => ['nullable', 'date_format:Y-m-d', ...($this->isMethod('post') ? ['after_or_equal:today'] : [])],
            'description' => ['required', 'string', 'min:20', 'max:1500'],
            'criteria' => ['nullable', 'string', 'max:2000'],
            'guarantees' => ['nullable', 'string', 'max:1000'],
            'limits' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Une certification porte déjà ce nom.',
            'short_name.unique' => 'Ce nom court est déjà utilisé par une autre certification.',
            'expires_at.after_or_equal' => 'La date d\'expiration ne peut pas être déjà passée.',
        ];
    }

    /** Validated data, with the multi-line fields split into lists. */
    public function certificationData(): array
    {
        $data = $this->validated();

        foreach (self::LIST_FIELDS as $field) {
            $data[$field] = Str::of($data[$field] ?? '')->explode("\n")->map(fn (string $line) => trim($line))->filter()->values()->all();
        }

        return $data;
    }
}
