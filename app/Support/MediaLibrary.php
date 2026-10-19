<?php

namespace App\Support;

use App\Models\Media;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Stores uploads for the media library. Photos are turned upright, scaled down to
 * MAX_WIDTH and re-encoded (which also drops camera and location metadata); GIFs are
 * kept as uploaded so animations survive. A small WebP thumbnail is made for the grids.
 */
class MediaLibrary
{
    public const MAX_WIDTH = 2400;

    public const THUMB_WIDTH = 480;

    /** Uploads on the public disk: library uploads (media/) and post images (posts/). */
    public const PATH_PATTERN = '~^(media|posts)/[A-Za-z0-9_-]+\.(jpe?g|png|webp|avif|gif)$~';

    public const RULES = ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:8192', 'dimensions:max_width=8000,max_height=8000'];

    public const MESSAGES = [
        'mimes' => 'Upload a JPG, PNG, WebP or GIF image.',
        'mimetypes' => 'Upload a JPG, PNG, WebP or GIF image.',
        'max' => 'Images can be up to 8 MB.',
        'dimensions' => 'Images can be up to 8000 pixels wide or tall.',
    ];

    public static function store(UploadedFile $file, ?int $userId = null, ?string $alt = null): Media
    {
        $disk = Storage::disk('public');
        $base = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), 60, '') ?: 'image';
        $extension = strtolower($file->extension() === 'jpeg' ? 'jpg' : $file->extension());
        $path = 'media/'.$base.'-'.Str::lower(Str::random(6)).'.'.$extension;

        $image = $extension === 'gif' ? null : self::load($file->getRealPath(), $extension);
        if ($image) {
            $image = self::scale(self::orient($image, $file->getRealPath(), $extension), self::MAX_WIDTH);
            $disk->put($path, self::encode($image, $extension));
        } else {
            $disk->putFileAs('media', $file, basename($path));
        }

        $absolute = $disk->path($path);
        [$width, $height] = @getimagesize($absolute) ?: [null, null];
        $media = new Media(['path' => $path]);
        self::thumbnail($image ?? self::load($absolute, $extension), $media->thumbPath());

        return Media::create([
            'path' => $path,
            'name' => Str::limit($file->getClientOriginalName(), 180, ''),
            'mime' => $disk->mimeType($path) ?: $file->getMimeType(),
            'size' => $disk->size($path),
            'width' => $width,
            'height' => $height,
            'alt' => $alt !== null ? Html::text($alt, 300) : null,
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Adds a file that is already on the public disk (a post image) to the
     * library, with a thumbnail. Returns the existing record if it is already there.
     */
    public static function register(?string $path, ?int $userId = null, ?string $name = null, ?string $alt = null): ?Media
    {
        $disk = Storage::disk('public');
        if (! $path || ! preg_match(self::PATH_PATTERN, $path) || ! $disk->exists($path)) {
            return null;
        }

        $media = Media::firstOrNew(['path' => $path]);
        if (! $media->exists) {
            $absolute = $disk->path($path);
            [$width, $height] = @getimagesize($absolute) ?: [null, null];
            self::thumbnail(self::load($absolute, strtolower(pathinfo($path, PATHINFO_EXTENSION))), $media->thumbPath());
            $media->fill([
                'name' => Str::limit($name ?: basename($path), 180, ''),
                'mime' => $disk->mimeType($path) ?: null,
                'size' => $disk->size($path),
                'width' => $width,
                'height' => $height,
                'uploaded_by' => $userId,
            ]);
        }
        if ($alt && ! $media->alt) {
            $media->alt = Html::text($alt, 300);
        }
        $media->save();

        return $media;
    }

    /**
     * Called after a post stops using a file: the file (and its library record)
     * is deleted unless another post still uses it.
     */
    public static function release(?string $path): void
    {
        if (! $path) {
            return;
        }
        $media = Media::firstWhere('path', $path);
        if (! $media) {
            Storage::disk('public')->delete($path);

            return;
        }
        if ($media->usage()->isEmpty()) {
            $media->deleteFiles();
            $media->delete();
        }
    }

    private static function load(string $path, string $extension): ?GdImage
    {
        try {
            $image = match ($extension) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($path),
                'avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
                'png' => @imagecreatefrompng($path),
                'webp' => @imagecreatefromwebp($path),
                'gif' => @imagecreatefromgif($path),
                default => false,
            };
        } catch (Throwable) {
            return null;
        }
        if (! $image) {
            return null;
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private static function orient(GdImage $image, string $path, string $extension): GdImage
    {
        if ($extension !== 'jpg' || ! function_exists('exif_read_data')) {
            return $image;
        }
        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private static function scale(GdImage $image, int $max): GdImage
    {
        $width = imagesx($image);
        if ($width <= $max) {
            return $image;
        }
        $height = (int) round(imagesy($image) * $max / $width);
        $scaled = imagecreatetruecolor($max, $height);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $max, $height, $width, imagesy($image));

        return $scaled;
    }

    private static function encode(GdImage $image, string $extension): string
    {
        ob_start();
        match ($extension) {
            'png' => imagepng($image, null, 8),
            'webp' => imagewebp($image, null, 82),
            default => imagejpeg($image, null, 82),
        };

        return (string) ob_get_clean();
    }

    private static function thumbnail(?GdImage $image, string $path): void
    {
        if (! $image) {
            return;
        }
        ob_start();
        imagewebp(self::scale($image, self::THUMB_WIDTH), null, 76);
        Storage::disk('public')->put($path, (string) ob_get_clean());
    }
}
