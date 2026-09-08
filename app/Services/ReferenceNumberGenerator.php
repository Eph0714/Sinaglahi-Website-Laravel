<?php

namespace App\Services;

/** Generates human-shareable but non-guessable application reference numbers, e.g. "SIN-2026-7F3K9Q". */
class ReferenceNumberGenerator
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I to avoid ambiguity

    public static function generate(): string
    {
        $chars = '';
        for ($i = 0; $i < 6; $i++) {
            $chars .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return 'SIN-'.date('Y')."-{$chars}";
    }
}
