<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'sitesettings';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    /** The settings row is a singleton (always Id = 1), matching the .NET model. */
    public static function current(): self
    {
        return static::query()->firstOrNew(['Id' => 1]);
    }
}
