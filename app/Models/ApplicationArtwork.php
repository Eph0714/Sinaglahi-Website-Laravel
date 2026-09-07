<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationArtwork extends Model
{
    protected $table = 'applicationartworks';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['CreatedAt' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'MembershipApplicationId', 'Id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'CategoryId', 'Id');
    }

    public function medium(): BelongsTo
    {
        return $this->belongsTo(ArtMedium::class, 'MediumId', 'Id');
    }
}
