<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkImage extends Model
{
    protected $table = 'artworkimages';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsPrimary' => 'boolean',
        ];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class, 'ArtworkId', 'Id');
    }
}
