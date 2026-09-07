<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutContent extends Model
{
    protected $table = 'aboutcontents';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    public function coreValues(): HasMany
    {
        return $this->hasMany(CoreValue::class, 'AboutContentId', 'Id')->orderBy('DisplayOrder');
    }

    /** The About content row is a singleton, matching the .NET model. */
    public static function current(): ?self
    {
        return static::query()->first();
    }
}
