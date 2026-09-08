<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Composes a single display "full name" from separate name fields, and
 * computes age from a birth date - mirrors Sinaglahi.Web.Services.PersonalNameHelper.
 */
class PersonalNameHelper
{
    public static function composeFullName(string $firstName, ?string $middleInitial, string $lastName, ?string $extensionName): string
    {
        $middle = trim((string) $middleInitial) === '' ? '' : ' '.rtrim(trim($middleInitial), '.').'.';
        $extension = trim((string) $extensionName) === '' ? '' : ' '.trim($extensionName);

        return trim(str_replace('  ', ' ', trim($firstName).$middle.' '.trim($lastName).$extension));
    }

    public static function calculateAge(string $dateOfBirth): int
    {
        return Carbon::parse($dateOfBirth)->age;
    }
}
