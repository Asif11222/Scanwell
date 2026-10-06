<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\NutrientThresholdRule;

class HealthRuleCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:150'],
            'target' => ['required', 'string', 'max:80'],
            'operator' => ['required', Rule::in(['>=', '>', '<=', '<', '=', 'contains'])],
            'threshold' => ['nullable', new NutrientThresholdRule()],
            'unit' => ['nullable', 'string', 'max:30'],
            'severity' => ['required', Rule::in([
                'Red / High Concern',
                'Yellow / Use With Caution',
                'Green / Looks Okay',
            ])],
            'health_concern_id' => ['nullable', 'exists:health_concerns,id'],
            'concern' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['Draft', 'Review', 'Published', 'Archived'])],
            'priority' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($value > 90 && $this->input('severity') === 'Green / Looks Okay') {
                        $fail('Positive green guidance rules should normally have priority <= 90 to give precedence to urgent clinical safety flags.');
                    }
                },
            ],
            'message' => ['required', 'string', 'min:8', 'max:500'],
            'recommendation' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a descriptive name for this health evaluation rule.',
            'target.required' => 'You must select a target nutrient, additive, or allergen to evaluate.',
            'operator.in' => 'Comparison operator must be one of: >=, >, <=, <, =, or contains.',
            'severity.in' => 'Please classify severity under Red (High Concern), Yellow (Use With Caution), or Green (Looks Okay).',
            'priority.between' => 'Rule priority must be configured between 1 (lowest) and 100 (highest urgency).',
            'message.required' => 'The alert message shown to consumers when this rule triggers cannot be blank.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'health rule name',
            'target' => 'target ingredient/nutrient',
            'operator' => 'evaluation operator',
            'threshold' => 'trigger threshold',
            'severity' => 'health caution severity',
            'message' => 'consumer advisory copy',
        ];
    }
}
