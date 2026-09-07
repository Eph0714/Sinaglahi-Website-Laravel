<?php

namespace App\Services;

use Illuminate\Contracts\Hashing\Hasher;
use RuntimeException;

/**
 * Reads and writes password hashes in ASP.NET Core Identity's default
 * "V3" PBKDF2 format, so accounts created by the original .NET app keep
 * working here without a forced password reset, and any new accounts
 * created by this app remain readable by the .NET app during the
 * transition period.
 *
 * Format (see Microsoft.AspNetCore.Identity.PasswordHasher<T>), all
 * integers big-endian / network byte order:
 *   byte 0        = 0x01 (format marker for V3)
 *   bytes 1-4     = KeyDerivationPrf enum (0=SHA1, 1=SHA256, 2=SHA512)
 *   bytes 5-8     = iteration count
 *   bytes 9-12    = salt size in bytes
 *   next N bytes  = salt
 *   remaining     = derived subkey
 */
class AspNetIdentityHasher implements Hasher
{
    private const FORMAT_MARKER_V3 = 0x01;

    private const PRF_SHA256 = 1;

    private const ITERATIONS = 100000;

    private const SALT_SIZE = 16;

    private const SUBKEY_SIZE = 32;

    public function info($hashedValue)
    {
        return ['algo' => 'aspnet-identity-v3', 'algoName' => 'aspnet-identity-v3', 'options' => []];
    }

    public function make(#[\SensitiveParameter] $value, array $options = [])
    {
        $salt = random_bytes(self::SALT_SIZE);
        $subkey = hash_pbkdf2('sha256', $value, $salt, self::ITERATIONS, self::SUBKEY_SIZE, true);

        $bytes = chr(self::FORMAT_MARKER_V3)
            .pack('N', self::PRF_SHA256)
            .pack('N', self::ITERATIONS)
            .pack('N', self::SALT_SIZE)
            .$salt
            .$subkey;

        return base64_encode($bytes);
    }

    public function check(#[\SensitiveParameter] $value, $hashedValue, array $options = [])
    {
        if ($hashedValue === '' || $hashedValue === null) {
            return false;
        }

        $bytes = base64_decode($hashedValue, true);
        if ($bytes === false || strlen($bytes) < 13) {
            return false;
        }

        $marker = ord($bytes[0]);
        if ($marker !== self::FORMAT_MARKER_V3) {
            // V2 (SHA1, fixed 1000 iterations, no embedded params) - rare on a
            // modern Identity install, but handled so an old row never just
            // silently fails to log in.
            return $this->checkV2($value, $bytes);
        }

        $prf = unpack('N', substr($bytes, 1, 4))[1];
        $iterations = unpack('N', substr($bytes, 5, 4))[1];
        $saltSize = unpack('N', substr($bytes, 9, 4))[1];
        $salt = substr($bytes, 13, $saltSize);
        $subkey = substr($bytes, 13 + $saltSize);

        $algo = match ($prf) {
            0 => 'sha1',
            2 => 'sha512',
            default => 'sha256',
        };

        $expected = hash_pbkdf2($algo, $value, $salt, $iterations, strlen($subkey), true);

        return hash_equals($expected, $subkey);
    }

    private function checkV2(string $value, string $bytes): bool
    {
        if (strlen($bytes) !== 1 + 16 + 20) {
            return false;
        }
        $salt = substr($bytes, 1, 16);
        $subkey = substr($bytes, 17, 20);
        $expected = hash_pbkdf2('sha1', $value, $salt, 1000, 20, true);

        return hash_equals($expected, $subkey);
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

    public function verifyConfiguration($value): bool
    {
        throw new RuntimeException('Not applicable.');
    }
}
