<?php

namespace Modules\Accounts\Policies\Role;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\User\User;

class UserRolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function viewAny(Credential $credential)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Credential $credential
     * @param Role $role
     * @param Company $company
     * @return mixed
     */
    public function view(Credential $credential, Role $role, Company $company)
    {
        if (auth('api')->user()->tokenCan('role_user_accounts_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('role_user_accounts_list_company')){
             if( auth('api')->user()->user->company_id == $company->id ){
                 return CompanyRole::where('company_id',$company->id)->where('role_id',$role->id)->exists();
             }
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Credential $credential
     * @param User $user
     * @return mixed
     */
    public function create(Credential $credential, User $user)
    {
        if (auth('api')->user()->tokenCan('user_account_roles_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_roles_update_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param array $users
     * @return mixed
     */
    public function update(Credential $credential, array $users)
    {
        if (auth('api')->user()->tokenCan('user_account_roles_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_roles_update_company')){
            $check = true;
            foreach ($users as $user){
                $user = User::findOrFail($user['user_id']);
                if( auth('api')->user()->user->company_id != $user->company_id ){
                    $check = false;
                }
            }
            return $check;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @param User $user
     * @return mixed
     */
    public function delete(Credential $credential, User $user)
    {
        if (auth('api')->user()->tokenCan('user_account_roles_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_roles_update_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param  UserRole  $credentialsRoles
     * @return mixed
     */
    public function restore(Credential $credential, UserRole $credentialsRoles)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param  UserRole  $credentialsRoles
     * @return mixed
     */
    public function forceDelete(Credential $credential, UserRole $credentialsRoles)
    {
        //
    }
}
