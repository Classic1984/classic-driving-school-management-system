<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLocalGovernmentArea implements ValidationRule
{
    public function __construct(protected ?string $state) {}

    /**
     * Confirm the given local government area actually belongs to the
     * selected state of origin. Silently passes if either side is blank or
     * the state isn't recognized, since state_of_origin has its own rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value || ! $this->state) {
            return;
        }

        $lgas = config('nigeria.states')[$this->state] ?? null;

        if ($lgas === null) {
            return;
        }

        if (! in_array($value, $lgas, true)) {
            $fail('The selected local government area is not valid for the chosen state of origin.');
        }
    }
}
