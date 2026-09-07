<?php

namespace App\Services;

use App\Models\ArtMedium;
use Illuminate\Support\Collection;

/**
 * Shared query/compose logic for the standardized Medium dropdown - mirrors
 * Sinaglahi.Web.Services.ArtMediumHelper, used identically by the Artist
 * area, Admin area, and the public Join wizard.
 */
class ArtMediumHelper
{
    /** Active mediums, ordered by DisplayOrder, with "Other" always forced last. */
    public static function getActiveOptions(): Collection
    {
        return ArtMedium::query()->active()
            ->orderBy('DisplayOrder')->orderBy('Name')
            ->get()
            ->sortBy(fn (ArtMedium $m) => $m->IsOtherOption ? 1 : 0)
            ->values();
    }

    /** Same as getActiveOptions() but also includes the artwork's current medium
     * even if it has since been deactivated. */
    public static function getOptionsForEdit(?int $currentMediumId): Collection
    {
        $options = static::getActiveOptions();
        if ($currentMediumId && ! $options->contains('Id', $currentMediumId)) {
            $current = ArtMedium::query()->find($currentMediumId);
            if ($current) {
                $entry = (clone $current);
                $entry->Name = $current->Name.' (inactive)';
                $insertAt = $options->search(fn (ArtMedium $o) => $o->IsOtherOption);
                if ($insertAt === false) {
                    $options->push($entry);
                } else {
                    $options = $options->take($insertAt)->push($entry)->merge($options->slice($insertAt));
                }
            }
        }

        return $options->values();
    }

    public static function getOtherOption(): ?ArtMedium
    {
        return ArtMedium::query()->where('IsOtherOption', true)->first();
    }

    /** The plain display string to store on Artwork/ApplicationArtwork.Medium. */
    public static function composeDisplayMedium(string $mediumName, bool $isOtherOption, ?string $customMedium): string
    {
        return $isOtherOption ? trim((string) $customMedium) : $mediumName;
    }
}
