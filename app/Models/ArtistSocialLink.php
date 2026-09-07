<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistSocialLink extends Model
{
    protected $table = 'artistsociallinks';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'ArtistId', 'Id');
    }
}
