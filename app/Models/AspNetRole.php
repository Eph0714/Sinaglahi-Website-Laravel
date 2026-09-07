<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AspNetRole extends Model
{
    protected $table = 'aspnetroles';

    protected $primaryKey = 'Id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AspNetUser::class, 'aspnetuserroles', 'RoleId', 'UserId', 'Id', 'Id');
    }
}
