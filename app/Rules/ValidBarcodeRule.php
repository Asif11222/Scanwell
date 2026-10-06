<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBarcodeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail("The :attribute must be a valid numerical barcode.");
            return;
        }

        $barcode = trim((string) $value);

        // Disallow empty or trivially short fake test codes
        if (strlen($barcode) < 6 || strlen($barcode) > 20) {
            $fail("The :attribute '{$barcode}' is invalid. Product barcodes must be between 6 and 20 digits.");
            return;
        }

        // Standard GS1/UPC/EAN format check (digits only or standard GS1-128 alphanumeric)
        if (! preg_match('/^[0-9A-Za-z\-_]+$/', $barcode)) {
            $fail("The :attribute '{$barcode}' contains forbidden characters. Only numbers and standard barcode characters are accepted.");
            return;
        }

        // Detect obvious placeholder barcodes
        if (preg_match('/^(0{6,}|1{6,}|123456)/', $barcode)) {
            $fail("The barcode '{$barcode}' looks like a placeholder or dummy sequence. Please provide a genuine retail product barcode.");
            return;
        }
    }
}
