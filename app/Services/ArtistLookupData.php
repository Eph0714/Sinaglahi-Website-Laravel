<?php

namespace App\Services;

/**
 * Shared, fixed option lists used by every artist-enrollment form - mirrors
 * Sinaglahi.Web.Services.ArtistLookupData so Province/Specialization/Medium
 * offer the exact same choices as the .NET app.
 */
class ArtistLookupData
{
    /** All 82 Philippine provinces (NCR/Metro Manila included), alphabetical. */
    public const PROVINCES = [
        'Abra', 'Agusan del Norte', 'Agusan del Sur', 'Aklan', 'Albay', 'Antique', 'Apayao', 'Aurora',
        'Basilan', 'Bataan', 'Batanes', 'Batangas', 'Benguet', 'Biliran', 'Bohol', 'Bukidnon', 'Bulacan',
        'Cagayan', 'Camarines Norte', 'Camarines Sur', 'Camiguin', 'Capiz', 'Catanduanes', 'Cavite', 'Cebu',
        'Cotabato', 'Davao de Oro', 'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental',
        'Dinagat Islands', 'Eastern Samar', 'Guimaras', 'Ifugao', 'Ilocos Norte', 'Ilocos Sur', 'Iloilo',
        'Isabela', 'Kalinga', 'La Union', 'Laguna', 'Lanao del Norte', 'Lanao del Sur', 'Leyte', 'Maguindanao del Norte',
        'Maguindanao del Sur', 'Marinduque', 'Masbate', 'Metro Manila (NCR)', 'Misamis Occidental', 'Misamis Oriental',
        'Mountain Province', 'Negros Occidental', 'Negros Oriental', 'Northern Samar', 'Nueva Ecija', 'Nueva Vizcaya',
        'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Pampanga', 'Pangasinan', 'Quezon', 'Quirino', 'Rizal',
        'Romblon', 'Samar', 'Sarangani', 'Siquijor', 'Sorsogon', 'South Cotabato', 'Southern Leyte', 'Sultan Kudarat',
        'Sulu', 'Surigao del Norte', 'Surigao del Sur', 'Tarlac', 'Tawi-Tawi', 'Zambales', 'Zamboanga del Norte',
        'Zamboanga del Sur', 'Zamboanga Sibugay',
    ];

    public const NUEVA_VIZCAYA = 'Nueva Vizcaya';

    public const SPECIALIZATIONS = [
        'Painting', 'Sculpture', 'Photography', 'Digital Art', 'Illustration',
        'Printmaking', 'Ceramics / Pottery', 'Mixed Media', 'Textile / Weaving', 'Woodcarving',
    ];

    public const MEDIUMS = [
        'Oil', 'Acrylic', 'Watercolor', 'Charcoal', 'Pastel', 'Ink',
        'Digital', 'Clay / Ceramics', 'Wood', 'Metal', 'Textile', 'Mixed Media',
    ];

    /** Joins the checked options plus a free-text "Others" value into the single
     * string the Artist.Specialization/PreferredMedium columns store. */
    public static function combineSelections(?array $chosen, ?string $otherText): ?string
    {
        $parts = array_values(array_filter($chosen ?? [], fn ($c) => trim((string) $c) !== ''));
        if (trim((string) $otherText) !== '') {
            $parts[] = trim($otherText);
        }

        return $parts === [] ? null : implode(', ', $parts);
    }

    /**
     * Splits a previously-stored combined string back into which known
     * checkboxes should be checked, plus whatever's left over as the "Others" text.
     *
     * @return array{0: array<int, string>, 1: ?string}
     */
    public static function parseSelections(array $knownOptions, ?string $stored): array
    {
        if (trim((string) $stored) === '') {
            return [[], null];
        }

        $pieces = array_filter(array_map('trim', explode(',', $stored)), fn ($p) => $p !== '');
        $checked = [];
        $leftovers = [];
        foreach ($pieces as $piece) {
            $match = collect($knownOptions)->first(fn ($o) => mb_strtolower($o) === mb_strtolower($piece));
            if ($match !== null) {
                $checked[] = $match;
            } else {
                $leftovers[] = $piece;
            }
        }

        return [$checked, $leftovers === [] ? null : implode(', ', $leftovers)];
    }
}
