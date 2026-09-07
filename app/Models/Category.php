<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
        ];
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'CategoryId', 'Id');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
