<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidBarcodeRule;

class ProductCustomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the custom validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->input('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:180',
                Rule::unique('products', 'name')->ignore($productId),
                function ($attribute, $value, $fail) {
                    if (preg_match('/^[0-9\s\W]+$/', $value)) {
                        $fail('The product name must contain genuine alphabetic brand or product terms, not purely numbers or symbols.');
                    }
                },
            ],
            'brand' => ['required', 'string', 'min:2', 'max:100'],
            'barcode' => [
                'required',
                new ValidBarcodeRule(),
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'category' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::in(['Published', 'Draft', 'Archived'])],
            'country' => ['nullable', 'string', 'max:80'],
            'serving_size' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:100'],
            'ingredients' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! empty($value) && strlen(trim($value)) < 3) {
                        $fail('Ingredients list appears too short. Please provide the declared package ingredient statement.');
                    }
                },
            ],
            'verified' => ['boolean'],
            'nutrition' => ['nullable', 'array'],
            'nutrition.*' => ['nullable', 'string', 'max:50'],
            'nutrient_names' => ['nullable', 'array'],
            'nutrient_names.*' => ['nullable', 'string', 'max:80'],
            'nutrient_values' => ['nullable', 'array'],
            'nutrient_values.*' => ['nullable', 'numeric', 'min:0'],
            'calories' => ['nullable', 'numeric', 'min:0'],
            'sugar' => ['nullable', 'numeric', 'min:0'],
            'added_sugar' => ['nullable', 'numeric', 'min:0'],
            'sodium' => ['nullable', 'numeric', 'min:0'],
            'fat' => ['nullable', 'numeric', 'min:0'],
            'saturated_fat' => ['nullable', 'numeric', 'min:0'],
            'trans_fat' => ['nullable', 'numeric', 'min:0'],
            'protein' => ['nullable', 'numeric', 'min:0'],
            'fiber' => ['nullable', 'numeric', 'min:0'],
            'carbohydrate' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance with custom cross-field rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $nameVal = trim((string)$this->input('name'));
            if ($nameVal !== '') {
                $productId = $this->route('product')?->id ?? $this->input('id');
                $nameExists = \App\Models\Product::whereRaw('LOWER(name) = ?', [strtolower($nameVal)])
                    ->when($productId, fn($q) => $q->where('id', '!=', $productId))
                    ->exists();
                if ($nameExists) {
                    $validator->errors()->add('name', 'This product name already exists in the catalog.');
                }
            }

            $filledCount = 0;

            // 1. Check dynamic nutrient arrays if submitted & ensure uniqueness
            if ($this->has('nutrient_names') && is_array($this->input('nutrient_names'))) {
                $names = $this->input('nutrient_names');
                $values = $this->input('nutrient_values', []);
                $seenNutrients = [];

                foreach ($names as $idx => $nName) {
                    $trimmedName = trim((string)$nName);
                    if ($trimmedName === '') continue;

                    // Normalize name to detect duplicate nutrient declarations (e.g. Sugar (g) vs Sugar)
                    $baseClean = strtolower(preg_replace('/\s*\([^)]*\)/', '', $trimmedName));
                    $cleanKey = str_replace([' ', '_', '-'], '', $baseClean) ?: strtolower($trimmedName);

                    if (isset($seenNutrients[$cleanKey])) {
                        $validator->errors()->add('nutrient_names', 'The nutrient already exists');
                        break;
                    }
                    $seenNutrients[$cleanKey] = true;

                    $val = $values[$idx] ?? null;
                    if ($val !== null && trim((string)$val) !== '' && is_numeric($val) && (float)$val >= 0) {
                        $filledCount++;
                    }
                }
            }

            // 2. Check legacy top-level nutrient fields if dynamic arrays were not supplied
            if ($filledCount === 0 && !$this->has('nutrient_names')) {
                $nutrientFields = [
                    'calories',
                    'sugar',
                    'added_sugar',
                    'sodium',
                    'fat',
                    'saturated_fat',
                    'trans_fat',
                    'protein',
                    'fiber',
                    'carbohydrate',
                ];

                foreach ($nutrientFields as $field) {
                    if ($this->has($field)) {
                        $val = $this->input($field);
                        if ($val !== null && trim((string)$val) !== '' && is_numeric($val) && (float)$val >= 0) {
                            $filledCount++;
                        }
                    }
                }
            }

            // 3. Also support direct nutrition key-value array payload
            if ($filledCount === 0 && $this->has('nutrition') && is_array($this->input('nutrition'))) {
                foreach ($this->input('nutrition') as $val) {
                    if ($val !== null && trim((string)$val) !== '') {
                        $filledCount++;
                    }
                }
            }

            if ($filledCount < 3) {
                $validator->errors()->add(
                    'nutrition',
                    'At least 3 nutritional declaration fields must be filled with valid data to evaluate the product (currently ' . $filledCount . ' provided). Other fields are optional.'
                );
            }
        });
    }

    /**
     * Custom validation messages with domain-specific guidance.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name must!',
            'name.unique' => 'This product name already exists in the catalog.',
            'name.min' => 'Product name must be at least 2 characters long.',
            'brand.required' => 'Please identify the brand or manufacturer of this product.',
            'barcode.required' => 'Barcode is required for retail lookup and scanner matching.',
            'barcode.unique' => 'A product with this exact barcode is already registered in the ScanWell catalog.',
            'category.required' => 'Please categorize this item under an approved food/beverage category.',
            'status.in' => 'Product status must be one of: Published, Draft, or Archived.',
            'nutrition.required' => 'At least 3 nutritional declaration fields must be provided.',
            'image.image' => 'The uploaded file must be a valid image.',
            'image.mimes' => 'The product picture must be a file of type: jpeg, png, jpg, webp, gif.',
            'image.max' => 'The product picture size may not exceed 5MB.',
        ];
    }

    /**
     * Custom human-friendly attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'product title',
            'brand' => 'brand label',
            'barcode' => 'retail barcode',
            'category' => 'food category',
            'serving_size' => 'serving size declaration',
            'ingredients' => 'ingredient statement',
            'image' => 'product picture',
        ];
    }
}
