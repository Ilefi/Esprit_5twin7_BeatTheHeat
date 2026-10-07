<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills a unique `slug` from `name` on create. Slugs never change afterwards, so public URLs stay stable.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(fn (self $model) => $model->slug ??= self::uniqueSlug($model->name));
    }

    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($i = 2; self::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
