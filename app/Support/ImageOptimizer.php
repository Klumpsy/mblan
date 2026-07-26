<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Shrinks and recompresses uploaded images in place so the server disk does not
 * fill up with multi-megabyte camera photos. Keeps the original file format and
 * path (so stored references never break) and only writes the result back when
 * it is actually smaller, which makes it safe to run on any existing upload.
 */
class ImageOptimizer
{
    public function __construct(
        private int $maxDimension = 1920,
        private int $jpegQuality = 82,
        private int $webpQuality = 82,
    ) {
    }

    public function optimize(string $path, string $disk = 'public'): void
    {
        // GD needs a real path on the local filesystem; skip cloud disks safely.
        if (config("filesystems.disks.{$disk}.driver") !== 'local') {
            return;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return;
        }

        $absolute = $storage->path($path);
        $info = @getimagesize($absolute);

        if ($info === false) {
            return;
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        // Decoding into GD needs the whole bitmap in RAM (~4 bytes per pixel,
        // plus the resized copy). Skip absurdly large images rather than risk an
        // out-of-memory crash, and raise the limit for the merely-big ones.
        $pixels = $width * $height;

        if ($pixels > 60_000_000) {
            return;
        }

        $this->ensureMemoryFor($pixels * 4 * 3);

        // GIFs are left untouched so we never flatten an animation.
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absolute),
            'image/png' => @imagecreatefrompng($absolute),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : null,
            default => null,
        };

        if (! $image) {
            return;
        }

        if ($mime === 'image/jpeg') {
            $image = $this->applyExifOrientation($image, $absolute);
            $width = imagesx($image);
            $height = imagesy($image);
        }

        $scale = min(1, $this->maxDimension / max($width, $height));
        $resized = $scale < 1;

        if ($resized) {
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));
            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            if ($canvas === false) {
                imagedestroy($image);

                return;
            }

            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $canvas;
        }

        $encoded = $this->encode($image, $mime);
        imagedestroy($image);

        if ($encoded === null) {
            return;
        }

        // Only overwrite when we actually saved bytes, or when we downscaled the
        // dimensions (a smaller image is worth keeping even at similar byte size).
        if ($resized || strlen($encoded) < (int) $storage->size($path)) {
            $storage->put($path, $encoded);
        }
    }

    private function encode(\GdImage $image, string $mime): ?string
    {
        ob_start();

        $ok = false;

        switch ($mime) {
            case 'image/jpeg':
                imageinterlace($image, true);
                $ok = imagejpeg($image, null, $this->jpegQuality);
                break;
            case 'image/png':
                imagesavealpha($image, true);
                $ok = imagepng($image, null, 6);
                break;
            case 'image/webp':
                $ok = imagewebp($image, null, $this->webpQuality);
                break;
        }

        $data = ob_get_clean();

        return ($ok && is_string($data) && $data !== '') ? $data : null;
    }

    /**
     * Raise memory_limit (never lower it) so a large decode fits. Raising is
     * always permitted by PHP; if the limit is already unlimited we leave it.
     */
    private function ensureMemoryFor(int $bytesNeeded): void
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '-1' || $limit === '') {
            return;
        }

        $needed = memory_get_usage(true) + $bytesNeeded;

        if ($this->toBytes($limit) < $needed) {
            @ini_set('memory_limit', (string) $needed);
        }
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;
        $unit = strtolower($value[strlen($value) - 1] ?? '');

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function applyExifOrientation(\GdImage $image, string $absolute): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($absolute);
        $orientation = $exif['Orientation'] ?? 0;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        // imagerotate returns false on failure; fall back to the original.
        return $rotated instanceof \GdImage ? $rotated : $image;
    }
}
