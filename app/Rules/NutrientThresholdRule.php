<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NutrientThresholdRule implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $operator = $this->data['operator'] ?? '>=';

        if (in_array($operator, ['>=', '>', '<=', '<', '='])) {
            if ($value === null || $value === '' || ! is_numeric($value)) {
                $fail("The threshold must be a valid numerical value when using mathematical comparison operators like '{$operator}'.");
                return;
            }

            if ((float) $value < 0) {
                $fail("Nutritional threshold values cannot be negative numbers.");
                return;
            }
        }
    }
}
