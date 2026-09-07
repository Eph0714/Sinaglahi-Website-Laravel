<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMenuItem extends Model
{
    protected $table = 'navigationmenuitems';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    // Mirrors MenuLocation in the .NET model.
    public const LOCATION_MAIN_NAV = 0;

    public const LOCATION_FOOTER_EXPLORE = 1;

    public const LOCATION_FOOTER_MEMBERSHIP = 2;

    protected function casts(): array
    {
        return [
            'IsExternal' => 'boolean',
            'OpenInNewTab' => 'boolean',
            'IsActive' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    public function scopeAt($query, int $location)
    {
        return $query->where('Location', $location);
    }
}
