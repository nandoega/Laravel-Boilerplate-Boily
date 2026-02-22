<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * File-based cache-aside helpers.
 *
 * Uses the 'file' driver intentionally — keeps Redis free-tier memory clean.
 * Redis is ONLY used for rate limiting and queue jobs.
 */
trait HasCaching
{
    /**
     * Get the cache driver from config (defaults to 'file').
     */
    protected function cacheDriver(): \Illuminate\Cache\Repository
    {
        $driver = config('api.cache.driver', 'file');
        return Cache::driver($driver);
    }

    /**
     * Build a deterministic cache key.
     */
    protected function cacheKey(string $suffix): string
    {
        $model = class_basename(static::class);
        return "api:{$model}:{$suffix}";
    }

    /**
     * Remember value in file cache.
     */
    protected function remember(string $key, \Closure $callback, ?int $ttl = null): mixed
    {
        if (!config('api.cache.enabled', true)) {
            return $callback();
        }

        $ttl ??= config('api.cache.ttl', 300);
        return $this->cacheDriver()->remember($key, $ttl, $callback);
    }

    /**
     * Forget a single cache key.
     */
    protected function forget(string $key): void
    {
        $this->cacheDriver()->forget($key);
    }

    /**
     * Forget multiple cache keys at once (e.g., on model update).
     */
    protected function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            $this->cacheDriver()->forget($key);
        }
    }

    /**
     * Clear all keys matching a given prefix pattern (file driver: iterate).
     * Note: Not efficient for huge datasets — use for small model lists only.
     */
    protected function flushByPrefix(string $prefix): void
    {
        // Wrapped in try/catch since some drivers don't support prefix flush
        try {
            Cache::driver(config('api.cache.driver', 'file'))->flush();
        } catch (\Throwable) {
            // Silently fail — data will expire via TTL
        }
    }
}
