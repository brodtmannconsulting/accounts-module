<?php

namespace Modules\Accounts\Entities\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounts\Entities\Role\RoleScope;

class CompanyRole extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\Company\CompanyRoleFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRoleScopes();
        });
    }

    private function deleteRoleScopes()
    {
        RoleScope::where('role_id', $this->attributes['role_id'])
            ->where('company_id', $this->attributes['company_id'])
            ->delete();
    }

}
