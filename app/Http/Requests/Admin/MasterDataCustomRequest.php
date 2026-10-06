<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterDataCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Custom validation rules for Master Data taxonomy entries.
     */
    public function rules(): array
    {
        $validTypes = [
            'Categories',
            'Brands',
            'Countries',
            'Nutrients',
            'Units',
            'Ingredients',
            'Additives',
            'Allergens',
            'Health Concerns',
        ];

        $masterData = $this->route('masterData');
        $masterDataId = is_object($masterData) ? $masterData->id : ($masterData ?? $this->input('id'));

        return [
            'type' => [
                'required',
                'string',
                Rule::in($validTypes),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                function ($attribute, $value, $fail) {
                    $clean = trim((string) $value);
                    if (strlen($clean) < 2) {
                        $fail('The taxonomy name is too short. Please provide a clear, recognized terminology.');
                    }
                    if (preg_match('/^[\s\W]+$/', $clean)) {
                        $fail('The name cannot consist purely of symbols or whitespace.');
                    }
                },
                // Custom uniqueness within the same taxonomy type
                Rule::unique('master_data', 'name')->where(function ($query) {
                    return $query->where('type', $this->input('type'));
                })->ignore($masterDataId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Bespoke domain-specific feedback messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select the target master data taxonomy domain.',
            'type.in' => 'Selected taxonomy group is not recognized in the governance system.',
            'name.required' => 'A valid taxonomy term/name is mandatory.',
            'name.min' => 'Taxonomy terms must be at least 2 characters.',
            'name.max' => 'Taxonomy term exceeds the 100 character allowable limit.',
            'name.unique' => 'This entry already exists within the selected master data taxonomy.',
        ];
    }
}
