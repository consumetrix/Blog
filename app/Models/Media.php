<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = ['path', 'name', 'mime', 'size', 'width', 'height', 'alt', 'uploaded_by'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function thumbPath(): string
    {
        return pathinfo($this->path, PATHINFO_DIRNAME).'/thumbs/'.pathinfo($this->path, PATHINFO_FILENAME).'.webp';
    }

    public function thumbUrl(): string
    {
        $disk = Storage::disk('public');

        return $disk->exists($this->thumbPath()) ? $disk->url($this->thumbPath()) : $this->url();
    }

    public function sizeLabel(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1).' MB'
            : max(1, (int) round($this->size / 1024)).' KB';
    }

    /**
     * Where the image is used: the posts that have it as their image.
     *
     * @return Collection<int, array{label: string, url: ?string}>
     */
    public function usage(): Collection
    {
        return Post::query()->where('image_path', $this->path)->orderBy('title')->get()
            ->map(fn (Post $post) => ['label' => 'Post: '.$post->title, 'url' => route('dashboard.posts.edit', $post)])
            ->values();
    }

    public function deleteFiles(): void
    {
        Storage::disk('public')->delete([$this->path, $this->thumbPath()]);
    }
}
