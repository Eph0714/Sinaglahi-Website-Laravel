<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;

/**
 * Validates and stores uploaded images under public/uploads - mirrors
 * Sinaglahi.Web.Services.FileStorageService: extension allow-list, size
 * limit, an actual image decode (rejects disguised executables), a random
 * non-guessable filename, and a generated thumbnail.
 */
class FileStorageService
{
    private const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB

    private const MIN_DIMENSION_PIXELS = 200;

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private string $publicRoot;

    public function __construct()
    {
        $this->publicRoot = public_path('uploads');
        if (! is_dir($this->publicRoot)) {
            mkdir($this->publicRoot, 0755, true);
        }
    }

    /** Saves a public-facing image (artist profile photo, artwork, etc.) under public/uploads/{subfolder}. */
    public function savePublicImage(UploadedFile $file, string $subfolder): ImageUploadResult
    {
        $validation = $this->validateAndDecode($file);
        if (! $validation['ok']) {
            return ImageUploadResult::fail($validation['error']);
        }

        $folder = $this->ensureFolder($subfolder);
        $fileName = $this->randomName($validation['extension']);
        $fullPath = $folder.DIRECTORY_SEPARATOR.$fileName;

        $this->writeImage($validation['image'], $validation['extension'], $fullPath);
        imagedestroy($validation['image']);

        return ImageUploadResult::ok("/uploads/{$subfolder}/{$fileName}");
    }

    /** Same as savePublicImage() but also writes a resized thumbnail alongside the full image. */
    public function savePublicImageWithThumbnail(UploadedFile $file, string $subfolder, int $thumbMax = 600): ImageUploadResult
    {
        $validation = $this->validateAndDecode($file);
        if (! $validation['ok']) {
            return ImageUploadResult::fail($validation['error']);
        }

        $folder = $this->ensureFolder($subfolder);
        $fileName = $this->randomName($validation['extension']);
        $thumbName = $this->randomName('jpg', '_thumb');
        $fullPath = $folder.DIRECTORY_SEPARATOR.$fileName;
        $thumbPath = $folder.DIRECTORY_SEPARATOR.$thumbName;

        $this->writeImage($validation['image'], $validation['extension'], $fullPath);

        $thumb = $this->resize($validation['image'], $thumbMax, $thumbMax);
        $this->writeImage($thumb, 'jpg', $thumbPath, 88);
        imagedestroy($thumb);
        imagedestroy($validation['image']);

        return ImageUploadResult::ok("/uploads/{$subfolder}/{$fileName}", "/uploads/{$subfolder}/{$thumbName}");
    }

    /** Deletes a previously saved public image (and its thumbnail, if any). */
    public function deletePublicImage(?string $storedPath, ?string $thumbnailPath = null): void
    {
        foreach ([$storedPath, $thumbnailPath] as $path) {
            if (! $path) {
                continue;
            }

            $relative = ltrim($path, '/');
            $combined = realpath(public_path()).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $webRoot = realpath(public_path());

            // Defend against path traversal: resolved path must stay inside the public root.
            $resolvedDir = realpath(dirname($combined));
            if ($resolvedDir === false || ! str_starts_with($resolvedDir, $webRoot)) {
                continue;
            }

            if (is_file($combined)) {
                @unlink($combined);
            }
        }
    }

    private function ensureFolder(string $subfolder): string
    {
        $folder = $this->publicRoot.DIRECTORY_SEPARATOR.$subfolder;
        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        return $folder;
    }

    private function randomName(string $extension, string $suffix = ''): string
    {
        return bin2hex(random_bytes(16)).$suffix.'.'.$extension;
    }

    /**
     * Section 27/43-equivalent security checks: extension allow-list, size
     * limit, and an actual image decode via GD (a renamed .exe fails to
     * decode and is rejected here, rather than trusted from its extension).
     *
     * @return array{ok: bool, error?: string, extension?: string, image?: GdImage}
     */
    private function validateAndDecode(UploadedFile $file): array
    {
        if (! $file->isValid() || $file->getSize() === 0) {
            return ['ok' => false, 'error' => 'The uploaded file is empty.'];
        }

        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            return ['ok' => false, 'error' => 'Each image must be 10 MB or smaller.'];
        }

        $extension = mb_strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return ['ok' => false, 'error' => 'Only JPG, PNG, and WebP images are allowed.'];
        }

        $path = $file->getRealPath();
        $info = @getimagesize($path);
        if ($info === false) {
            return ['ok' => false, 'error' => 'The file does not appear to be a valid image.'];
        }

        [$width, $height] = $info;
        if ($width < self::MIN_DIMENSION_PIXELS || $height < self::MIN_DIMENSION_PIXELS) {
            return ['ok' => false, 'error' => sprintf('Image must be at least %dx%d pixels.', self::MIN_DIMENSION_PIXELS, self::MIN_DIMENSION_PIXELS)];
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };

        if ($image === false) {
            return ['ok' => false, 'error' => 'The file does not appear to be a valid image.'];
        }

        return ['ok' => true, 'extension' => $extension === 'jpeg' ? 'jpg' : $extension, 'image' => $image];
    }

    private function writeImage(GdImage $image, string $extension, string $path, int $quality = 88): void
    {
        match ($extension) {
            'png' => imagepng($image, $path),
            'webp' => imagewebp($image, $path, $quality),
            default => imagejpeg($image, $path, $quality),
        };
    }

    /** Resizes to fit within maxW x maxH (Mode: Max - preserves aspect ratio, never upscales). */
    private function resize(GdImage $image, int $maxW, int $maxH): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min($maxW / $width, $maxH / $height, 1.0);
        $newW = max(1, (int) round($width * $ratio));
        $newH = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newW, $newH);
        imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);

        return $resized;
    }
}
