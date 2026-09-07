<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtMedium extends Model
{
    protected $table = 'artmediums';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
            'IsOtherOption' => 'boolean',
        ];
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'MediumId', 'Id');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
