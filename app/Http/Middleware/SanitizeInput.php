<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sanitize all string inputs: strip HTML tags to prevent XSS stored in DB.
 * Does NOT encode — encoding is the view layer's responsibility.
 * Skips: file uploads, null values, non-string types.
 */
class SanitizeInput
{
    /**
     * Keys to never sanitize (e.g., passwords must remain untouched).
     */
    private array $skipKeys = ['password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, Closure $next): Response
    {
        $cleaned = $this->sanitize($request->all());
        $request->merge($cleaned);

        return $next($request);
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->skipKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $data[$key] = strip_tags(trim($value));
            }
        }

        return $data;
    }
}
