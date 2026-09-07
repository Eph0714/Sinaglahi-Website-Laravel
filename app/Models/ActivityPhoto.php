<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPhoto extends Model
{
    protected $table = 'activityphotos';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsCover' => 'boolean',
            'IsHidden' => 'boolean',
            'PhotoDate' => 'datetime',
            'UploadedDate' => 'datetime',
            'UpdatedDate' => 'datetime',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'ActivityId', 'Id');
    }
}
