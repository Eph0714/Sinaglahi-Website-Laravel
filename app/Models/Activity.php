<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $table = 'activities';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    // Mirrors ActivityStatus in the .NET model.
    public const STATUS_UPCOMING = 0;

    public const STATUS_ONGOING = 1;

    public const STATUS_COMPLETED = 2;

    public function statusLabel(): string
    {
        return match ((int) $this->Status) {
            self::STATUS_ONGOING => 'Ongoing',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Upcoming',
        };
    }

    protected function casts(): array
    {
        return [
            'ActivityDate' => 'datetime',
            'IsFeatured' => 'boolean',
            'IsPublished' => 'boolean',
            'CreatedDate' => 'datetime',
            'UpdatedDate' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'CategoryId', 'Id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ActivityPhoto::class, 'ActivityId', 'Id')->orderBy('DisplayOrder');
    }

    public function visiblePhotos(): HasMany
    {
        return $this->photos()->where('IsHidden', false);
    }

    public function scopePublished($query)
    {
        return $query->where('IsPublished', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('IsFeatured', true);
    }
}
