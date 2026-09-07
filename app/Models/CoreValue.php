<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreValue extends Model
{
    protected $table = 'corevalues';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
        ];
    }

    public function aboutContent(): BelongsTo
    {
        return $this->belongsTo(AboutContent::class, 'AboutContentId', 'Id');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
