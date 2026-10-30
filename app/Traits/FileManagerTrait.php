<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Shared upload helper for admin controllers. Files are written straight
 * into public/{folder} (not the storage disk) because every restored
 * model's accessor builds URLs as `url('/') . "/upload/..."` — e.g.
 * Product::getCoverImageAttribute(), Category::getCoverImageAttribute().
 * Only the bare filename is persisted to the database; the accessor
 * prepends the folder + host at read time.
 */
trait FileManagerTrait
{
    public function upload(string $folder, UploadedFile $file, ?string $oldFilename = null): string
    {
        $folder = trim($folder, '/');
        $destination = public_path($folder);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $extension = $this->safeExtension($file);

        if ($oldFilename) {
            $this->delete($folder, $oldFilename);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $extension;
        $file->move($destination, $filename);

        return $filename;
    }

    /**
     * Extension taken from the file's actual contents, never the name the
     * browser sent — otherwise a valid JPEG named "x.html" would be stored
     * as .html in the public folder and served as a web page. Anything
     * that isn't an image or video is refused outright.
     *
     * @throws ValidationException
     */
    private function safeExtension(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->guessExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if (!in_array($extension, self::$allowedUploadExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => 'Only JPG, PNG, WEBP, GIF or AVIF images and MP4, MOV or WEBM videos can be uploaded.',
            ]);
        }

        return $extension;
    }

    private static array $allowedUploadExtensions = ['jpg', 'png', 'webp', 'gif', 'avif', 'mp4', 'mov', 'webm'];

    public function delete(string $folder, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = public_path(trim($folder, '/') . '/' . basename($filename));

        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Resize $sourceUrl (an already-uploaded image, referenced by its
     * public URL) down to $width x $height and save it as $filename under
     * public/{folder}. Used for product cover_image_thumbnail.
     */
    public function generateThumbnail(string $folder, string $filename, string $sourceUrl, int $width, int $height, int $quality = 90, ?string $oldFilename = null): ?string
    {
        $folder = trim($folder, '/');
        $destination = public_path($folder);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        if ($oldFilename) {
            $this->delete($folder, $oldFilename);
        }

        $sourcePath = public_path(parse_url($sourceUrl, PHP_URL_PATH) ?? '');

        if (!is_file($sourcePath) || !extension_loaded('gd')) {
            return null;
        }

        [$origWidth, $origHeight, $type] = getimagesize($sourcePath);

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : null,
            default => null,
        };

        if (!$source) {
            return null;
        }

        $thumb = imagecreatetruecolor($width, $height);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        $destPath = $destination . '/' . $filename;
        imagejpeg($thumb, $destPath, $quality);

        imagedestroy($source);
        imagedestroy($thumb);

        return $filename;
    }
}
