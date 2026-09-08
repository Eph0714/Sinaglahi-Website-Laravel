<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $table = 'artists';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    // Mirrors Artist.AccountStatus in the .NET model (ArtistAccountStatus enum).
    public const STATUS_PENDING = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 2;

    public const STATUS_REJECTED = 3;

    protected function casts(): array
    {
        return [
            'IsVerified' => 'boolean',
            'IsProfilePublic' => 'boolean',
            'IsFeatured' => 'boolean',
            'DateOfBirth' => 'datetime',
            'VerifiedDate' => 'datetime',
            'ApprovedDate' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AspNetUser::class, 'UserId', 'Id');
    }

    public function statusLabel(): string
    {
        return match ((int) $this->AccountStatus) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending',
        };
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'ArtistId', 'Id');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(ArtistSocialLink::class, 'ArtistId', 'Id');
    }

    /** Mirrors Artist.IsPubliclyVisible in the .NET model. */
    public function isPubliclyVisible(): bool
    {
        return $this->IsVerified
            && (int) $this->AccountStatus === self::STATUS_ACTIVE
            && $this->IsProfilePublic;
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('IsVerified', true)
            ->where('AccountStatus', self::STATUS_ACTIVE)
            ->where('IsProfilePublic', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('IsFeatured', true);
    }
}
