<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorrectionReviewCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Approved', 'Rejected', 'Review', 'Pending'])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please provide a valid decision status for this product correction.',
            'status.in' => 'Review status must be one of: Approved, Rejected, Review, or Pending.',
        ];
    }
}
