<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUserPermission extends Model
{
    protected $table = 'adminuserpermissions';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['IsGranted' => 'boolean'];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'PermissionId', 'Id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AspNetUser::class, 'UserId', 'Id');
    }
}
