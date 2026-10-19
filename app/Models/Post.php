<?php

namespace App\Models;

use App\Support\Html;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    /** Categories. Keys are stored and used in ?type= filters. */
    public const TYPES = [
        'case-study' => 'Case Study',
        'article' => 'Article',
        'analysis' => 'Analysis',
        'field-notes' => 'Field Notes',
    ];

    public const STATUSES = ['draft' => 'Draft', 'published' => 'Published'];

    protected $fillable = [
        'slug', 'type', 'title', 'excerpt', 'body', 'image_path', 'image_alt',
        'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    /**
     * Published and already live, newest first.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /** live, scheduled or draft. */
    public function state(): string
    {
        if ($this->status !== 'published') {
            return 'draft';
        }

        return $this->published_at?->isFuture() ? 'scheduled' : 'live';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? Str::headline($this->type);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * The body as safe HTML. Bodies are saved as rich text (the dashboard's editor); one still in
     * the earlier plain-text form is converted on the way out.
     */
    public function bodyHtml(): string
    {
        return Html::html(self::isHtml($this->body) ? $this->body : self::textToHtml((string) $this->body));
    }

    public static function isHtml(?string $body): bool
    {
        return str_starts_with(ltrim((string) $body), '<');
    }

    /**
     * The earlier plain-text form: blank lines separate paragraphs, a line break stays a line
     * break, and a paragraph starting with "## " is a subheading.
     */
    public static function textToHtml(string $text): string
    {
        $chunks = preg_split("/\R\s*\R/", trim(str_replace("\r\n", "\n", $text))) ?: [];

        return implode('', array_map(function (string $chunk) {
            $chunk = trim($chunk);

            return str_starts_with($chunk, '## ')
                ? '<h2>'.e(trim(substr($chunk, 3))).'</h2>'
                : '<p>'.nl2br(e($chunk), false).'</p>';
        }, array_filter($chunks, fn ($c) => trim($c) !== '')));
    }

    public function summary(int $limit = 180): string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }

        preg_match('#<p>(.*?)</p>#s', $this->bodyHtml(), $first);
        $text = html_entity_decode(strip_tags(str_replace('<br>', ' ', $first[1] ?? '')), ENT_QUOTES | ENT_HTML5);

        return Str::limit(Str::squish($text), $limit);
    }
}
