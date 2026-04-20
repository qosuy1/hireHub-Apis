<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CleanContentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $forbiddenWords = ['مسيء1', 'مسيء2', 'badword']; 

        foreach ($forbiddenWords as $word) {
            if (mb_stripos($value, $word) !== false) {
                $fail('نعتذر، النص يحتوي على كلمات غير لائقة أو محتوى مسيء.');
                return;
            }
        }
    }
}
