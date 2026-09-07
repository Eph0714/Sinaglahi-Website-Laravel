<?php

if (! function_exists('placeholder_image')) {
    /**
     * A simple inline SVG placeholder (data URI) for records without an
     * uploaded image yet - mirrors the .NET app's Media/Placeholder action
     * without needing a dedicated route.
     */
    function placeholder_image(int $w, int $h, string $label): string
    {
        $safeLabel = htmlspecialchars(mb_substr($label, 0, 24), ENT_QUOTES);
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="{$w}" height="{$h}" viewBox="0 0 {$w} {$h}">
            <rect width="100%" height="100%" fill="#2B2B2B"/>
            <text x="50%" y="50%" fill="#A8A8A8" font-family="sans-serif" font-size="16"
                  text-anchor="middle" dominant-baseline="middle">{$safeLabel}</text>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
