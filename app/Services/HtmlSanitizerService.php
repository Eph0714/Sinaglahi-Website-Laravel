<?php

namespace App\Services;

/**
 * Sanitizes rich-text HTML from the Pages CMS editor before it's stored, so
 * admin-authored content can be rendered unescaped without becoming a
 * stored-XSS vector (e.g. if an admin account is ever compromised). Mirrors
 * the .NET app's HtmlSanitizerService allow-list (headings, paragraphs,
 * bold/italic, lists, links, images, quotes - nothing script-capable),
 * implemented without an external dependency: strip_tags() for the
 * tag allow-list, plus regex passes to strip event-handler attributes and
 * javascript:/data: URIs that strip_tags alone would leave inside allowed tags.
 */
class HtmlSanitizerService
{
    private const ALLOWED_TAGS = '<h1><h2><h3><p><br><b><strong><i><em><ul><ol><li><a><img><blockquote><span>';

    public function sanitize(?string $html): string
    {
        if (trim((string) $html) === '') {
            return '';
        }

        // Drop <script>/<style> blocks (tag AND inner content) before
        // strip_tags runs - strip_tags only removes the tags themselves and
        // would otherwise leave raw script/style source sitting as visible text.
        $clean = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html);

        $clean = strip_tags($clean, self::ALLOWED_TAGS);

        // Strip event-handler attributes (onclick=, onerror=, etc.).
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $clean);

        // Strip javascript:/data: URIs from href/src.
        $clean = preg_replace('/\s+(href|src)\s*=\s*("javascript:[^"]*"|\'javascript:[^\']*\'|"data:[^"]*"|\'data:[^\']*\')/i', '', $clean);

        return trim($clean);
    }
}
