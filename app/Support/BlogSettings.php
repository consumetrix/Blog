<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Settings edited in Dashboard → Settings, stored as one row per group in the `settings` table.
 * Today there is one group, "general", with the "Under construction" switch.
 */
class BlogSettings
{
    private const CACHE_KEY = 'blog-settings';

    public static function defaults(): array
    {
        return ['general' => ['under_construction' => false]];
    }

    public static function all(): array
    {
        $stored = Cache::remember(self::CACHE_KEY, 300, function () {
            try {
                return Setting::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                return []; // before the first migration
            }
        });

        return array_replace_recursive(self::defaults(), $stored);
    }

    public static function get(string $group, string $key): mixed
    {
        return self::all()[$group][$key] ?? self::defaults()[$group][$key] ?? null;
    }

    /** While on, visitors see a holding page; signed-in users and the dashboard are unaffected. */
    public static function underConstruction(): bool
    {
        return (bool) self::get('general', 'under_construction');
    }

    public static function save(array $general, ?int $userId = null): void
    {
        Setting::updateOrCreate(['key' => 'general'], [
            'value' => ['under_construction' => (bool) ($general['under_construction'] ?? false)],
            'updated_by' => $userId,
        ]);
        Cache::forget(self::CACHE_KEY);
    }
}
