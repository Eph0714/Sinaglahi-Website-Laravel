<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsPublished' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('IsPublished', true);
    }
}
