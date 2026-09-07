<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationNote extends Model
{
    protected $table = 'applicationnotes';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'IsApplicantVisible' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'MembershipApplicationId', 'Id');
    }
}
