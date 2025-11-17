<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RateLimitService
{
    /**
     * Ensure the given key is within the allowed attempt threshold.
     * Throws a ValidationException with a user-friendly message if limited.
     */
    public function ensureAllowed(string $key, int $maxAttempts, int $decaySeconds, string $fieldName = 'email'): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                $fieldName => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }
    }

    /**
     * Hit the rate limiter for the given key.
     */
    public function hit(string $key, int $decaySeconds): void
    {
        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Clear the attempts for the given key (e.g., on success).
     */
    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }
}