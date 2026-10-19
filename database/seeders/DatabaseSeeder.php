<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Sample posts so a fresh install has something to show. Safe to run again: posts are matched by slug.
 * The dashboard account is created with `php artisan blog:admin you@example.com`.
 */
class DatabaseSeeder extends Seeder
{
    private const TYPES = ['INSIGHT' => 'article', 'ANALYSIS' => 'analysis', 'FIELD NOTE' => 'field-notes', 'CASE STUDY' => 'case-study'];

    public function run(): void
    {
        $articles = require resource_path('content/articles.php');

        $day = 0;
        foreach ($articles as $slug => $article) {
            Post::updateOrCreate(['slug' => $slug], [
                'type' => self::TYPES[$article['type']] ?? 'article',
                'title' => $article['title'],
                'body' => implode('', array_map(fn (string $p) => '<p>'.e($p).'</p>', $article['body'])),
                'status' => 'published',
                'published_at' => now()->subDays(++$day * 7),
            ]);
        }
    }
}
