<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $type = is_string($type) && isset(Post::TYPES[$type]) ? $type : null;

        $all = Post::query()->get(['id', 'type', 'status', 'published_at']);

        return view('dashboard.posts.index', [
            'posts' => Post::query()
                ->when($type, fn ($q) => $q->where('type', $type))
                ->orderByRaw('published_at IS NULL DESC')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get(),
            'type' => $type,
            'counts' => $all->countBy('type'),
            'stats' => [
                'live' => $all->filter(fn (Post $p) => $p->state() === 'live')->count(),
                'scheduled' => $all->filter(fn (Post $p) => $p->state() === 'scheduled')->count(),
                'drafts' => $all->filter(fn (Post $p) => $p->state() === 'draft')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('dashboard.posts.form', [
            'post' => new Post(['type' => 'article', 'status' => 'draft']),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = new Post;
        $this->save($post, $request);

        return redirect()->route('dashboard.posts.edit', $post)->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('dashboard.posts.form', [
            'post' => $post,
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->save($post, $request);

        return redirect()->route('dashboard.posts.edit', $post)->with('status', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $image = $post->image_path;
        $post->delete();
        MediaLibrary::release($image);
        ActivityLog::record('post.deleted', $post->title, ['type' => $post->type]);

        return redirect()->route('dashboard.posts.index')->with('status', 'Post deleted.');
    }

    /**
     * The image is added to the media library; a replaced or removed image is deleted
     * only if nothing else (a page, settings) uses it.
     */
    private function save(Post $post, PostRequest $request): void
    {
        $previous = $post->image_path;
        $creating = ! $post->exists;
        $this->fill($post, $request);
        $changed = array_keys($post->getDirty());
        $post->save();

        if ($previous && $previous !== $post->image_path) {
            MediaLibrary::release($previous);
        }
        MediaLibrary::register($post->image_path, $request->user()?->id, $post->title, $post->image_alt);

        ActivityLog::record($creating ? 'post.created' : 'post.updated', $post->title, $creating
            ? ['post' => $post->id, 'status' => $post->status]
            : ['post' => $post->id, 'changed' => array_values(array_diff($changed, ['updated_at']))]);
    }

    private function fill(Post $post, PostRequest $request): void
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        $post->fill($data);

        if ($request->boolean('remove_image') && $post->image_path) {
            $post->image_path = null;
            $post->image_alt = null;
        }

        if ($request->hasFile('image')) {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }
    }
}
