<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** Colours offered in the dashboard (the brand palette plus a few neutrals). Any #RRGGBB is accepted. */
    public const COLORS = ['#2BA3A0', '#1F7A78', '#C79A45', '#5B8DEF', '#B4432E', '#7A5CB5', '#5B5F66', '#132A52'];

    protected $fillable = ['slug', 'name', 'description', 'color', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug)) {
                $category->slug = static::uniqueSlug($category->name, $category->id);
            }
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /** The colour to draw this category's dot in; never empty. */
    public function dot(): string
    {
        return $this->color ?: '#5B5F66';
    }
}
