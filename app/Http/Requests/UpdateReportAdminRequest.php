<?php

namespace App\Http\Requests;

use App\View\Components\StatusBadge;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(StatusBadge::options('report')))],
            'priority' => ['required', 'string', 'in:'.implode(',', array_keys(StatusBadge::options('priority')))],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'resolution' => ['nullable', 'required_if:status,confirmed,rejected,resolved', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le statut du signalement est obligatoire.',
            'priority.required' => 'La priorité est obligatoire.',
            'resolution.required_if' => 'Une décision motivée est obligatoire pour clôturer ou valider le signalement.',
        ];
    }
}
