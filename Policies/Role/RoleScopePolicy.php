<?php

namespace Modules\Accounts\Policies\Role;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\RoleScope;

class RoleScopePolicy
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
     * @param  RoleScope  $rolesScopes
     * @return mixed
     */
    public function view(Credential $credential, RoleScope $rolesScopes)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function create(Credential $credential)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param array $scopes
     * @return mixed
     */
    public function update(Credential $credential, array $scopes)
    {
        if (auth()->user()->tokenCan('user_account_roles_update_all')){
            return true;
        }
        if (auth()->user()->tokenCan('user_account_roles_update_company')){
            return auth()->user()->user->hasScopes($scopes);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @param  \App\RoleScope  $rolesScopes
     * @return mixed
     */
    public function delete(Credential $credential, RoleScope $rolesScopes)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param  \App\RoleScope  $rolesScopes
     * @return mixed
     */
    public function restore(Credential $credential, RoleScope $rolesScopes)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param  \App\RoleScope  $rolesScopes
     * @return mixed
     */
    public function forceDelete(Credential $credential, RoleScope $rolesScopes)
    {
        //
    }
}
