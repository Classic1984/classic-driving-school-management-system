<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class StudentOtpService
{
    /**
     * How long a code stays valid after being sent.
     */
    protected const TTL_MINUTES = 10;

    /**
     * Generate a fresh 6-digit code for this phone number, overwriting any
     * still-valid code already issued to it, and cache it for later
     * verification.
     */
    public function generate(string $normalizedPhone): string
    {
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($normalizedPhone), $code, now()->addMinutes(self::TTL_MINUTES));

        return $code;
    }

    /**
     * Check a submitted code against the one on file for this phone
     * number. Correct on the first try only - the cached code is cleared
     * either way once checked, so a leaked or guessed code can't be
     * replayed and a wrong guess can't be retried against the same code
     * forever.
     */
    public function verify(string $normalizedPhone, string $code): bool
    {
        $key = $this->cacheKey($normalizedPhone);
        $cached = Cache::get($key);
        Cache::forget($key);

        return $cached !== null && hash_equals($cached, $code);
    }

    protected function cacheKey(string $normalizedPhone): string
    {
        return "student-otp:{$normalizedPhone}";
    }
}
