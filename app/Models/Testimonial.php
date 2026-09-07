<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $table = 'testimonials';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'Date' => 'datetime',
            'IsPublished' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('IsPublished', true);
    }
}
