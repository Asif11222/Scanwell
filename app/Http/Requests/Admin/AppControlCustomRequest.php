<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AppControlCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:80'],
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    $key = $this->input('key');
                    if (in_array($key, ['minVersion', 'latestVersion'])) {
                        if (! preg_match('/^\d+\.\d+\.\d+$/', (string) $value)) {
                            $fail("The version string '{$value}' must adhere to standard semantic versioning format (e.g. 1.4.0).");
                        }
                    }
                },
            ],
            'type' => ['nullable', 'string', 'in:boolean,string,integer,json'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'A valid configuration key name is required.',
            'value.required' => 'The runtime control value cannot be empty.',
        ];
    }
}
