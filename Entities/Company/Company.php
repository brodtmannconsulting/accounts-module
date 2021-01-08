<?php

namespace Modules\Accounts\Entities\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Accounts\Database\factories\Company\CompanyFactory;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\User\User;;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return CompanyFactory::new();
    }

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



    /**
     * Boot function for using with Company Events
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

        static::deleting(function ($model)
        {
            $model->deleteCompanyUsers();
            $model->deleteCompaniesRoles();
        });
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function deleteCompanyUsers() {
        $this->users()->delete ();
    }

    public function deleteCompaniesRoles() {
        RoleScope::where('company_id',$this->id)->delete ();
        $this->roles()->where ('is_custom',1)->delete ();
        CompanyRole::where('company_id',$this->id)->delete();
    }

    public function users(){
        return $this->hasMany (User::class);
    }

    /**
     * The roles that belong to the company.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'company_roles','company_id','role_id');
    }

    /**
     * The credentials that belong to the company users.
     */
    public function credentials()
    {
        $credentials = collect([]);
        $this->users ()->each (function ($user) use($credentials){
            $user->credentials()->each(function ($credential) use($credentials){
                $credentials->push($credential);
            });
        });
        $credentials = $credentials->unique();
        return $credentials;
    }


    public function path(){
        return '/companies/'. $this->id;
    }


}
