<?php

namespace Modules\Accounts\Entities\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounts\Database\factories\Role\UserRoleFactory;

class UserRole extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected static function newFactory()
    {
        return UserRoleFactory::new();
    }
}
