<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppContentCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contentId = $this->route('content')?->id ?? $this->input('id');

        return [
            'content_key' => [
                'required',
                'string',
                'min:4',
                'max:100',
                'regex:/^[a-z0-9]+(\.[a-z0-9_\-]+)+$/i',
                Rule::unique('app_contents', 'content_key')->ignore($contentId),
            ],
            'area' => ['required', Rule::in([
                'Onboarding',
                'Authentication',
                'Scan workflow',
                'Privacy',
                'Empty state',
                'Help',
                'Banner',
            ])],
            'locale' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/'],
            'title' => ['required', 'string', 'min:2', 'max:150'],
            'body' => ['required', 'string', 'min:4'],
            'status' => ['required', Rule::in(['Published', 'Review', 'Draft'])],
        ];
    }

    public function messages(): array
    {
        return [
            'content_key.regex' => 'The content key must follow dot-notation convention (e.g., onboarding.health_flags.title).',
            'content_key.unique' => 'This content key is already configured for this application.',
            'locale.regex' => 'Please provide a valid ISO locale code like en-US or bn-BD.',
        ];
    }
}
