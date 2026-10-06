<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\AdSafetyCheckRule;

class CampaignCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'type' => ['required', Rule::in(['Banner', 'Card', 'Sponsored collection', 'Full-screen'])],
            'headline' => [
                'required',
                'string',
                'min:5',
                'max:140',
                new AdSafetyCheckRule(),
            ],
            'copy' => [
                'required',
                'string',
                'min:10',
                'max:600',
                new AdSafetyCheckRule(),
            ],
            'cta' => ['required', 'string', 'max:50'],
            'cta_url' => ['required', 'string', 'max:255'],
            'placement' => ['required', Rule::in([
                'Home Banner',
                'Home Feed',
                'Search Results',
                'Product Details',
                'Scan Result',
                'Personalized Alerts',
                'Full-screen Promotion',
            ])],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'priority' => ['required', 'integer', 'between:1,100'],
            'frequency_cap' => ['required', 'integer', 'between:1,20'],
            'status' => ['required', Rule::in(['Active', 'Scheduled', 'Paused', 'Draft', 'Archived'])],
            'region' => ['nullable', 'string', 'max:80'],
            'segment' => ['nullable', 'string', 'max:80'],
            'health_target' => ['nullable', 'string', 'max:120'],
            'theme' => ['nullable', Rule::in(['violet', 'blue', 'green', 'amber'])],
        ];
    }

    public function messages(): array
    {
        return [
            'headline.required' => 'Campaign headline is required to introduce the sponsored feature.',
            'copy.required' => 'Detailed copy is required explaining the offering to users.',
            'end_at.after' => 'Campaign end date must be scheduled after the campaign start date.',
            'placement.in' => 'Please choose one of the predefined mobile app placement zones.',
        ];
    }
}
