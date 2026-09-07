<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artwork extends Model
{
    protected $table = 'artworks';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    // Mirrors ArtworkStatus in the .NET model.
    public const STATUS_DRAFT = 0;

    public const STATUS_PENDING_REVIEW = 1;

    public const STATUS_APPROVED = 2;

    public const STATUS_REJECTED = 3;

    public const STATUS_PUBLISHED = 4;

    public const STATUS_ARCHIVED = 5;

    protected function casts(): array
    {
        return [
            'IsAvailable' => 'boolean',
            'IsFeatured' => 'boolean',
            'Price' => 'decimal:2',
            'SubmittedAt' => 'datetime',
            'ApprovedAt' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'ArtistId', 'Id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'CategoryId', 'Id');
    }

    public function medium(): BelongsTo
    {
        return $this->belongsTo(ArtMedium::class, 'MediumId', 'Id');
    }

    public function additionalImages(): HasMany
    {
        return $this->hasMany(ArtworkImage::class, 'ArtworkId', 'Id')->orderBy('DisplayOrder');
    }

    public function scopePublished($query)
    {
        return $query->where('Status', self::STATUS_PUBLISHED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('IsFeatured', true);
    }
}
