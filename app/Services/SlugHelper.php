<?php

namespace App\Services;

/** Turns display names into SEO-friendly URL slugs, e.g. "Juan Dela Cruz" -> "juan-dela-cruz". */
class SlugHelper
{
    public static function generateSlug(string $phrase): string
    {
        $str = mb_strtolower(trim($phrase));
        $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
        $str = preg_replace('/-+/', '-', $str);

        return trim($str, '-');
    }
}
