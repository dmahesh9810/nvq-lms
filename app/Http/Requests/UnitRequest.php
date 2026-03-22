<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nvq_unit_code' => 'nullable|string|max:50',
            'learning_outcomes' => 'nullable|string',
            'performance_criteria' => 'nullable|string',
            'assessment_criteria' => 'nullable|string',
            'nvq_level' => 'nullable|integer|between:1,7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
