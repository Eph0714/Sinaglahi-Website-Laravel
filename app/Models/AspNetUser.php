<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * The ASP.NET Core Identity user account (table: aspnetusers), unified
 * across every role - Super Admin, Admin, and Artist alike, matching the
 * .NET app's ApplicationUser. String (GUID) primary key, not auto-incrementing.
 */
class AspNetUser extends Model implements Authenticatable
{
    use AuthenticatableTrait;
    use Authorizable;

    protected $table = 'aspnetusers';

    protected $primaryKey = 'Id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['PasswordHash', 'SecurityStamp', 'ConcurrencyStamp'];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
            'EmailConfirmed' => 'boolean',
            'PhoneNumberConfirmed' => 'boolean',
            'TwoFactorEnabled' => 'boolean',
            'LockoutEnabled' => 'boolean',
            'CreatedAt' => 'datetime',
            'LastLoginAt' => 'datetime',
            'LockoutEnd' => 'datetime',
        ];
    }

    // ---- Authenticatable overrides for Identity's column names ----

    public function getAuthPassword(): string
    {
        return (string) $this->PasswordHash;
    }

    public function getAuthPasswordName(): string
    {
        return 'PasswordHash';
    }

    public function getAuthIdentifierName(): string
    {
        return 'Id';
    }

    // ---- Relationships ----

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AspNetRole::class, 'aspnetuserroles', 'UserId', 'RoleId', 'Id', 'Id');
    }

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'AdminRoleId', 'Id');
    }

    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class, 'UserId', 'Id');
    }

    public function grantedPermissions(): HasMany
    {
        return $this->hasMany(AdminUserPermission::class, 'UserId', 'Id');
    }

    public function superAdminAssignment(): HasOne
    {
        return $this->hasOne(SuperAdminAssignment::class, 'UserId', 'Id');
    }

    // ---- Role helpers (mirrors Sinaglahi.Web.Models.Roles) ----

    public const ROLE_SUPER_ADMIN = 'SuperAdmin';

    public const ROLE_ADMIN = 'Admin';

    public const ROLE_ARTIST = 'Artist';

    public function hasRole(string $role): bool
    {
        return $this->roles->contains(fn (AspNetRole $r) => $r->Name === $role);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN) || $this->superAdminAssignment !== null;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isArtist(): bool
    {
        return $this->hasRole(self::ROLE_ARTIST);
    }
}
