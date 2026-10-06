<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AdSafetyCheckRule implements ValidationRule
{
    /**
     * Forbidden deceptive medical / health promise terms in advertising campaigns.
     *
     * @var string[]
     */
    protected array $forbiddenPhrases = [
        'cures diabetes',
        'cures cancer',
        'prevents disease',
        'guaranteed weight loss',
        'doctor certified miraculous',
        'no health risks whatsoever',
        'overrides health flags',
        'bypass health warning',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $lowercase = strtolower($value);
        foreach ($this->forbiddenPhrases as $forbidden) {
            if (str_contains($lowercase, $forbidden)) {
                $fail("The :attribute violates ScanWell ad safety policies by containing deceptive medical claims: '{$forbidden}'. Sponsored campaigns cannot promise clinical cures or suppress health warnings.");
                return;
            }
        }
    }
}
