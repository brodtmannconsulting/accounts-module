<?php

namespace Modules\Accounts\Entities\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounts\Database\factories\Role\RoleScopeFactory;

class RoleScope extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return RoleScopeFactory::new();
    }
}
