<?php

namespace App\Services;

class ImageUploadResult
{
    public bool $success = false;

    public ?string $error = null;

    public ?string $storedPath = null;

    public ?string $thumbnailPath = null;

    public static function fail(string $error): self
    {
        $result = new self;
        $result->error = $error;

        return $result;
    }

    public static function ok(string $storedPath, ?string $thumbnailPath = null): self
    {
        $result = new self;
        $result->success = true;
        $result->storedPath = $storedPath;
        $result->thumbnailPath = $thumbnailPath;

        return $result;
    }
}
