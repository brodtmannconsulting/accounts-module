<?php

namespace Modules\Accounts\Entities\Role;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Scope\Scope;
use Modules\Accounts\Entities\User\User;

class Role extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        return \Modules\Accounts\Database\factories\Role\RoleFactory::new();
    }

    /**
     * Boot function for using with Role Events
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $model->setIdAttribute();
        });
        self::deleting(function($role) {
            UserRole::where('role_id',$role->id)->get()->each(function($usersRoles) {
                $usersRoles->delete(); // <-- direct deletion
            });
            RoleScope::where('role_id',$role->id)->get()->each(function($usersRoles) {
                $usersRoles->delete(); // <-- direct deletion
            });
        });
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function scopes(){

        $company_id = auth()->user()->user->company_id;

        return $this->belongsToMany(
            Scope::class,
            'role_scopes',
            'role_id',
            'scope_id'
        )->where('company_id', $company_id);
    }

    public function companies(){
        return $this->belongsToMany (Company::class,'companies_roles');
    }

    public function users(){
        return $this->belongsToMany (User::class,'user_roles');
    }

    public function path(){
        return '/roles/'. $this->id;
    }


}
