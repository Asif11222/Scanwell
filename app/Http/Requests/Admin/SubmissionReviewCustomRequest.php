<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmissionReviewCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Approved', 'Rejected', 'Review', 'Pending'])],
            'action_type' => ['nullable', Rule::in(['approve', 'reject', 'correction', 'duplicate', 'merge'])],
            'note' => ['nullable', 'string', 'max:500'],
            'extracted_fields' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Submission status must be updated to Approved, Rejected, Review, or Pending.',
        ];
    }
}
