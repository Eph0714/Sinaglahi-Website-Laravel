<?php

namespace App\Models;

use App\Services\ApplicationStatusPresentation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipApplication extends Model
{
    protected $table = 'membershipapplications';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    // Mirrors ApplicationStatus in the .NET model.
    public const STATUS_DRAFT = 0;

    public const STATUS_SUBMITTED = 1;

    public const STATUS_PENDING_REVIEW = 2;

    public const STATUS_UNDER_REVIEW = 3;

    public const STATUS_ADDITIONAL_INFO_REQUIRED = 4;

    public const STATUS_APPROVED = 5;

    public const STATUS_REJECTED = 6;

    public const STATUS_WITHDRAWN = 7;

    public const STATUS_ARCHIVED = 8;

    protected function casts(): array
    {
        return [
            'HasExhibitionExperience' => 'boolean',
            'HasOtherOrganizationExperience' => 'boolean',
            'SellsOrDisplaysArtwork' => 'boolean',
            'ApplicantConsent' => 'boolean',
            'PrivacyConsent' => 'boolean',
            'ConsentDate' => 'datetime',
            'SubmittedAt' => 'datetime',
            'ReviewedAt' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DateOfBirth' => 'datetime',
        ];
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(ApplicationArtwork::class, 'MembershipApplicationId', 'Id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class, 'MembershipApplicationId', 'Id')->orderByDesc('CreatedAt');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'MembershipApplicationId', 'Id')->orderBy('ChangedAt');
    }

    public function statusLabel(): string
    {
        return ApplicationStatusPresentation::label((int) $this->Status);
    }

    /** CSS-class-safe version of the label (no spaces), e.g. "Under Review" -> "underreview". */
    public function statusCssSlug(): string
    {
        return str_replace(' ', '', mb_strtolower($this->statusLabel()));
    }
}
