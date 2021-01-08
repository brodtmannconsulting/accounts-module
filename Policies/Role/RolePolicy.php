<?php

namespace Modules\Accounts\Policies\Role;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;

class RolePolicy
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
        if (auth('api')->user()->tokenCan('system_roles_list_all')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Credential $credential
     * @param Role $constantsUserRole
     * @return mixed
     */
    public function view(Credential $credential, Role $constantsUserRole)
    {
        if (auth('api')->user()->tokenCan('system_roles_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('system_roles_list_company')){
            return $this->checkIfUserCanSeeThisRole($constantsUserRole);
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Credential $credential
     * @param array $scopesData
     * @return mixed
     */
    public function create(Credential $credential, array $scopesData)
    {
        if (auth('api')->user()->tokenCan('system_roles_add_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('system_roles_add_company')){
            //make array unique and make from multidimensional array a single array
            $company_ids = array_map("unserialize", array_unique(array_map("serialize", array_column($scopesData, 'companies_ids'))))[0];
            if($this->checkIfUserHasAccessToThisCompanies($company_ids)){
                $constants_scopes_ids = array_column($scopesData, 'scope_id');
                return $this->checkIfUserHasAccessToThisScopes($constants_scopes_ids);
            }
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param Role $constantsUserRole
     * @return mixed
     */
    public function update(Credential $credential, Role $constantsUserRole)
    {
        if ($credential->tokenCan('system_roles_update_all')){
            return true;
        }
        if ($credential->tokenCan('system_roles_update_company')){
             return $this->checkIfUserCanUpdateOrDeleteThisRole($constantsUserRole);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @param Role $constantsUserRole
     * @return mixed
     */
    public function delete(Credential $credential, Role $constantsUserRole)
    {
        if ($credential->tokenCan('system_roles_destroy_all')){
            return true;
        }
        if ($credential->tokenCan('system_roles_destroy_company')){
            return $this->checkIfUserCanUpdateOrDeleteThisRole($constantsUserRole);
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param Role $constantsUserRole
     * @return mixed
     */
    public function restore(Credential $credential, Role $constantsUserRole)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param Role $constantsUserRole
     * @return mixed
     */
    public function forceDelete(Credential $credential, Role $constantsUserRole)
    {
        //
    }

    private function checkIfUserHasAccessToThisCompanies(array $companies_ids): bool
    {
        if(in_array (auth('api')->user()->user->company_id,$companies_ids)){
            return sizeof ($companies_ids) == 1;
        }
        return false;
    }

    private function checkIfUserHasAccessToThisScopes(array $constants_scopes_ids): bool
    {
        $found = true;
        foreach($constants_scopes_ids as $scope_id) {
            if (!in_array($scope_id, auth ()->user ()->user->getScopesIds())) {
                $found = false;
                break;
            }
        }
        return $found;
    }

    private function checkIfUserCanUpdateOrDeleteThisRole(Role $constantsUserRole): bool
    {
        $custom_roles = auth('api')->user()->user->company()->first()->roles()->where('is_custom', 1)->get()->pluck('id')->toArray();
        if(!empty($custom_roles)){
            return in_array ($constantsUserRole->id,$custom_roles);
        }
        return false;
    }

    private function checkIfUserCanSeeThisRole(Role $constantsUserRole): bool
    {
        $constantsUserRole = auth('api')->user()->user->company()->first()->roles()->find($constantsUserRole->id);
        if($constantsUserRole){
            return true;
        }
        return false;
    }
}
