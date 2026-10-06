<?php

namespace App\Services;

use App\Models\HealthRule;

class HealthEvaluationService
{
    /**
     * Evaluate a product's nutritional declaration and ingredients against published health rules.
     *
     * @param  array<string, mixed>  $nutrition   Key-value pairs (e.g., ['Sodium' => '210 mg', 'Sugar' => '12 g'])
     * @param  string|null           $ingredients Ingredient statement text
     * @return array{flags_count: int, red_count: int, yellow_count: int, green_count: int, flags: array<string, array<mixed>>}
     */
    public function evaluate(array $nutrition = [], ?string $ingredients = null): array
    {
        // Fetch active/published health intelligence rules ordered by clinical priority
        $rules = HealthRule::whereIn('status', ['Published', 'Review'])
            ->orderBy('priority', 'desc')
            ->get();

        $matched = [
            'red' => [],
            'yellow' => [],
            'green' => [],
        ];

        // Normalize nutrition keys: e.g. "Added Sugar" -> "addedsugar", "Total Fat" -> "totalfat", "Sodium (mg)" -> "sodium"
        $normalizedNutrition = [];
        foreach ($nutrition as $key => $value) {
            $numVal = null;

            if (is_numeric($value)) {
                $numVal = (float) $value;
            } elseif (is_string($value) && preg_match('/([\d\.]+)/', $value, $m)) {
                $numVal = (float) $m[1];
            }

            // Direct key (e.g. "Sodium" -> "sodium", "Added Sugar" -> "addedsugar")
            $cleanKey = strtolower(str_replace([' ', '_', '-'], '', (string) $key));
            $normalizedNutrition[$cleanKey] = [
                'raw' => $value,
                'numeric' => $numVal,
            ];

            // Base key stripping parenthetical units (e.g. "Sodium (mg)" -> "Sodium" -> "sodium")
            $baseKey = preg_replace('/\s*\([^)]*\)/', '', (string) $key);
            $cleanBaseKey = strtolower(str_replace([' ', '_', '-'], '', (string) $baseKey));
            $normalizedNutrition[$cleanBaseKey] = [
                'raw' => $value,
                'numeric' => $numVal,
            ];
        }

        $ingredientsLower = strtolower($ingredients ?? '');

        foreach ($rules as $rule) {
            $isTriggered = false;
            $target = trim((string) $rule->target);
            $cleanTarget = strtolower(str_replace([' ', '_', '-'], '', $target));
            $operator = trim((string) $rule->operator);
            $threshold = is_numeric($rule->threshold) ? (float) $rule->threshold : null;

            if ($operator === 'contains') {
                // Ingredient / Additive presence check
                if ($target !== '' && str_contains($ingredientsLower, strtolower($target))) {
                    $isTriggered = true;
                }
            } else {
                // Numerical nutrient check (e.g. Sodium >= 200, Added Sugar >= 5, Trans Fat = 0)
                if (isset($normalizedNutrition[$cleanTarget]) && $threshold !== null && $normalizedNutrition[$cleanTarget]['numeric'] !== null) {
                    $val = $normalizedNutrition[$cleanTarget]['numeric'];

                    $isTriggered = match ($operator) {
                        '>=' => $val >= $threshold,
                        '>' => $val > $threshold,
                        '<=' => $val <= $threshold,
                        '<' => $val < $threshold,
                        '=', '==' => abs($val - $threshold) < 0.0001,
                        default => false,
                    };
                }
            }

            if ($isTriggered) {
                $flagData = [
                    'rule_id' => $rule->id,
                    'name' => $rule->name,
                    'target' => $rule->target,
                    'severity' => $rule->severity,
                    'concern' => $rule->concern,
                    'message' => $rule->message,
                    'recommendation' => $rule->recommendation,
                    'priority' => $rule->priority,
                ];

                $sevLower = strtolower($rule->severity);
                if (str_contains($sevLower, 'red') || str_contains($sevLower, 'high')) {
                    $matched['red'][] = $flagData;
                } elseif (str_contains($sevLower, 'yellow') || str_contains($sevLower, 'caution')) {
                    $matched['yellow'][] = $flagData;
                } else {
                    $matched['green'][] = $flagData;
                }
            }
        }

        // Total risk flags (Red + Yellow) shown on product badge
        $riskCount = count($matched['red']) + count($matched['yellow']);

        return [
            'flags_count' => $riskCount,
            'red_count' => count($matched['red']),
            'yellow_count' => count($matched['yellow']),
            'green_count' => count($matched['green']),
            'flags' => $matched,
        ];
    }
}
