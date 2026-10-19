<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Support\Html;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $media = Media::query()
            ->with('uploader')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('alt', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(48)
            ->withQueryString();

        $selected = $request->integer('media') ? Media::find($request->integer('media')) : null;

        return view('dashboard.media.index', [
            'media' => $media,
            'selected' => $selected,
            'usage' => $selected?->usage() ?? collect(),
            'search' => $search,
        ]);
    }

    /**
     * The original file, saved under the image's library name.
     */
    public function download(Media $media): StreamedResponse
    {
        $disk = Storage::disk('public');
        abort_unless($disk->exists($media->path), 404);

        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $base = Str::slug(Str::beforeLast($media->name, '.'.$extension)) ?: pathinfo($media->path, PATHINFO_FILENAME);

        return $disk->download($media->path, $base.'.'.$extension);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:20'],
            'images.*' => MediaLibrary::RULES,
        ], [
            'images.required' => 'Choose at least one image.',
            'images.max' => 'Upload up to 20 images at a time.',
            ...collect(MediaLibrary::MESSAGES)->mapWithKeys(fn ($m, $rule) => ["images.*.{$rule}" => $m])->all(),
        ]);

        $stored = collect($request->file('images'))->map(function ($file) use ($request) {
            $media = MediaLibrary::store($file, $request->user()->id);
            ActivityLog::record('upload.created', $media->name, ['media' => $media->id]);

            return $media;
        });

        $message = $stored->count() === 1 ? 'Image uploaded. Add a description so screen readers can describe it.' : $stored->count().' images uploaded.';

        return redirect()->route('dashboard.media.index', $stored->count() === 1 ? ['media' => $stored->first()->id] : [])
            ->with('status', $message);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'alt' => ['nullable', 'string', 'max:300'],
        ]);

        $media->update([
            'name' => Html::text($data['name'], 180),
            'alt' => Html::text($data['alt'] ?? '', 300) ?: null,
        ]);
        ActivityLog::record('media.updated', $media->name, ['media' => $media->id]);

        return redirect()->route('dashboard.media.index', ['media' => $media->id])->with('status', 'Image details saved.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $usage = $media->usage();
        if ($usage->isNotEmpty()) {
            return redirect()->route('dashboard.media.index', ['media' => $media->id])
                ->withErrors(['media' => 'This image is still used on: '.$usage->pluck('label')->join(', ').'. Replace it there first.']);
        }

        ActivityLog::record('media.deleted', $media->name, ['path' => $media->path]);
        $media->deleteFiles();
        $media->delete();

        return redirect()->route('dashboard.media.index')->with('status', 'Image deleted.');
    }
}
