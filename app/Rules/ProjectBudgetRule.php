<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ProjectBudgetRule implements ValidationRule
{
    public function __construct(protected string $type)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $PlatformRateRules = config('hireHub');

        if ($this->type === 'fixed' && $value < $PlatformRateRules['min_fixed_budget']) {
            $fail('Projects with fixed budget should not be less than ' . $PlatformRateRules['min_fixed_budget'] . ' $');
        }

        if ($this->type === 'hourly' && ($value < $PlatformRateRules['min_hourly_rate'] || $value > $PlatformRateRules['max_hourly_rate'])) {
            $fail("Hour Price should be between " . $PlatformRateRules['min_hourly_rate'] . " and " . $PlatformRateRules['max_hourly_rate']);
        }
    }
}
