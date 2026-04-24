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
        // we can get the forbidden words from a list of badwords and save it as an array in config file 
        $forbiddenWords = ['مسيء1', 'مسيء2', 'badword' ,'badword4' ,'badword5']; 

        foreach ($forbiddenWords as $word) {
            if (mb_stripos($value, $word) !== false) {
                $fail('We are sorry! but your ' . $attribute . ' have some forbidden words');
                return;
            }
        }
    }
}
