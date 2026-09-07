<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteBanner extends Model
{
    protected $table = 'sitebanners';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsEnabled' => 'boolean',
            'IsFeatured' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('IsEnabled', true);
    }
}
