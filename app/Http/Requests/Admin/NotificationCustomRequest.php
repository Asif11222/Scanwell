<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:5', 'max:500'],
            'audience' => ['required', 'string', 'max:100'],
            'schedule_time' => ['nullable', 'string', 'max:100'],
            'deep_link' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! str_starts_with($value, 'scanwell://') && ! filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The deep link must be a valid app URI scheme (e.g. scanwell://saved, scanwell://health-profile) or standard URL.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['Scheduled', 'Sent', 'Draft'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Notification title cannot be empty.',
            'body.required' => 'Notification body text is required.',
            'deep_link.required' => 'Please provide an in-app destination or deep link for this push alert.',
        ];
    }
}
