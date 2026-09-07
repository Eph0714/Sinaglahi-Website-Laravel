<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $table = 'adminroles';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['CreatedAt' => 'datetime'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(AspNetUser::class, 'AdminRoleId', 'Id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'adminrolepermissions', 'AdminRoleId', 'PermissionId', 'Id', 'Id');
    }
}
