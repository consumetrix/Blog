<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
        $counts = Post::visible()->get(['id', 'category_id'])->countBy('category_id');
        $categories = Category::ordered()->get()->filter(fn (Category $c) => $counts->get($c->id, 0) > 0)->values();

        $slug = $request->query('category');
        $category = is_string($slug) ? $categories->firstWhere('slug', $slug) : null;

        $posts = Post::visible()
            ->with('category')
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->paginate((int) config('blog.per_page'))
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'category' => $category,
            'counts' => $counts,
            'total' => $counts->sum(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::visible()->with('category')->where('slug', $slug)->firstOrFail();

        return view('posts.show', [
            'post' => $post,
            'related' => $post->category_id
                ? Post::visible()->with('category')->where('category_id', $post->category_id)->whereKeyNot($post->id)->limit(3)->get()
                : collect(),
        ]);
    }

    public function feed(): Response
    {
        $posts = Post::visible()->with('category')->limit((int) config('blog.feed_limit'))->get();

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
