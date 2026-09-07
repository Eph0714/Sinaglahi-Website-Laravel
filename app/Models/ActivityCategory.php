<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCategory extends Model
{
    protected $table = 'activitycategories';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'CategoryId', 'Id');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
