<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminAssignment extends Model
{
    protected $table = 'superadminassignments';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['AssignedAt' => 'datetime'];
    }
}
