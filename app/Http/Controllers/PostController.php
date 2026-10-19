<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * The public blog: the list (filterable by category), one post, the RSS feed and the sitemap.
 */
class PostController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $type = is_string($type) && isset(Post::TYPES[$type]) ? $type : null;

        $all = Post::visible()->get(['id', 'type']);
        $posts = Post::visible()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->paginate((int) config('blog.per_page'))
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'type' => $type,
            'counts' => $all->countBy('type'),
            'total' => $all->count(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::visible()->where('slug', $slug)->firstOrFail();

        return view('posts.show', [
            'post' => $post,
            'related' => Post::visible()->where('type', $post->type)->whereKeyNot($post->id)->limit(3)->get(),
        ]);
    }

    public function feed(): Response
    {
        $posts = Post::visible()->limit((int) config('blog.feed_limit'))->get();

        return response()
            ->view('feed', [
                'posts' => $posts,
                'updated' => $posts->max(fn (Post $post) => max($post->updated_at, $post->published_at)) ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $posts = Post::visible()->get(['slug', 'published_at', 'updated_at']);

        $urls = [['loc' => route('home'), 'priority' => '1.0', 'lastmod' => $posts->max(fn (Post $p) => max($p->updated_at, $p->published_at))]];
        foreach ($posts as $post) {
            $urls[] = ['loc' => route('posts.show', $post->slug), 'priority' => '0.7', 'lastmod' => max($post->updated_at, $post->published_at)];
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
