<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyJoinBenefit extends Model
{
    protected $table = 'whyjoinbenefits';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
