<?php

namespace Modules\Accounts\Entities\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyRole extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\Company\CompanyRoleFactory::new();
    }
}
