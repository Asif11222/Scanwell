<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthConcernCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $concernId = $this->route('concern')?->id ?? $this->input('id');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:80',
                Rule::unique('health_concerns', 'name')->ignore($concernId),
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:400'],
            'mapped_nutrients' => ['nullable'],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A unique condition or health goal name (e.g. Diabetes, Asthma) is required.',
            'name.unique' => 'A health concern taxonomy entry with this name already exists.',
        ];
    }
}
