<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DisposableEmail implements ValidationRule
{
    /**
     * Validate that the email domain is not a known disposable email provider.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = (string) $value;
        $atPos = strrpos($email, '@');

        if ($atPos === false) {
            return;
        }

        $domain = substr($email, $atPos + 1);

        if (in_array($domain, config('disposable-email.domains'), true)) {
            $fail(__('auth.disposable_email'));
        }
    }
}
