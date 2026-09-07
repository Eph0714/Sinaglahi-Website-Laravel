<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyJoinPhoto extends Model
{
    protected $table = 'whyjoinphotos';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
            'IsFeatured' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
