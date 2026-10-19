<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** Action => how it reads after the person's name. */
    public const LABELS = [
        'auth.login' => 'signed in',
        'auth.logout' => 'signed out',
        'auth.failed' => 'failed to sign in as',
        'auth.two_factor_enabled' => 'set up an authenticator app for',
        'auth.two_factor_failed' => 'entered a wrong authenticator code for',
        'auth.recovery_code_used' => 'signed in with a recovery code as',
        'auth.two_factor_reset' => 'reset the authenticator app of',
        'post.created' => 'created the post',
        'post.updated' => 'updated the post',
        'post.deleted' => 'deleted the post',
        'upload.created' => 'uploaded the image',
        'media.updated' => 'updated the image details for',
        'media.deleted' => 'deleted the image',
        'user.created' => 'added the user',
        'user.updated' => 'updated the user',
        'user.deleted' => 'deleted the user',
    ];

    /** Filter on the Activity screen => action prefixes. */
    public const AREAS = [
        'posts' => ['label' => 'Posts', 'prefixes' => ['post.']],
        'media' => ['label' => 'Media', 'prefixes' => ['upload.', 'media.']],
        'users' => ['label' => 'Users', 'prefixes' => ['user.']],
        'auth' => ['label' => 'Sign-ins', 'prefixes' => ['auth.']],
    ];

    protected $fillable = ['user_id', 'action', 'subject', 'properties', 'ip_address'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $subject = null, array $properties = [], ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject' => $subject ? mb_substr($subject, 0, 200) : null,
            'properties' => $properties ?: null,
            'ip_address' => app()->runningInConsole() && ! app()->runningUnitTests() ? null : request()?->ip(),
        ]);
    }

    public function label(): string
    {
        return self::LABELS[$this->action] ?? $this->action;
    }

    /**
     * Extra detail shown under the entry, built from the stored properties.
     */
    public function detail(): ?string
    {
        $p = $this->properties ?? [];

        return match (true) {
            isset($p['at']) => 'For '.$p['at'],
            isset($p['from'], $p['to']) && is_string($p['from']) => 'From '.$p['from'].' to '.$p['to'],
            isset($p['from_user']) => 'From '.$p['from_user'],
            isset($p['changed']) && $p['changed'] !== [] => 'Changed: '.implode(', ', array_map(fn ($f) => str_replace('_', ' ', $f), $p['changed'])),
            isset($p['status']) => 'Status: '.$p['status'],
            isset($p['role']) => 'Role: '.$p['role'],
            default => null,
        };
    }

    /** Property key => model the id in it points to. */
    public const LINKED = [
        'post' => Post::class,
        'media' => Media::class,
    ];

    /**
     * Which linked ids still exist, for a page of entries: ['page' => [3 => true], ...].
     *
     * @param  iterable<self>  $entries
     */
    public static function existing(iterable $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            foreach (self::LINKED as $key => $model) {
                if (is_int($entry->properties[$key] ?? null)) {
                    $ids[$key][] = $entry->properties[$key];
                }
            }
        }

        return collect($ids)->map(fn (array $list, string $key) => array_fill_keys(
            (self::LINKED[$key])::whereKey(array_unique($list))->pluck('id')->all(), true,
        ))->all();
    }

    /**
     * A dashboard link to the thing the entry is about, while it still exists.
     */
    public function link(?array $existing = null): ?string
    {
        $p = $this->properties ?? [];
        $exists = fn (string $key) => is_int($p[$key] ?? null) && ($existing === null
            ? (self::LINKED[$key])::whereKey($p[$key])->exists()
            : isset($existing[$key][$p[$key]]));

        return match (true) {
            $exists('post') => route('dashboard.posts.edit', $p['post']),
            $exists('media') => route('dashboard.media.index', ['media' => $p['media']]),
            default => null,
        };
    }

    /**
     * Narrows the log to the Activity screen's filters: person (an id, or "system"),
     * area, date range and a subject search.
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $person = $filters['person'] ?? null;
        $area = self::AREAS[$filters['area'] ?? ''] ?? null;

        return $query
            ->when($person === 'system', fn ($q) => $q->whereNull('user_id'))
            ->when(is_numeric($person), fn ($q) => $q->where('user_id', (int) $person))
            ->when($area, fn ($q) => $q->where(function ($q) use ($area) {
                foreach ($area['prefixes'] as $prefix) {
                    $q->orWhere('action', 'like', $prefix.'%');
                }
            }))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('subject', 'like', '%'.$term.'%'));
    }
}
