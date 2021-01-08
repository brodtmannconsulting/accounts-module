<?php

namespace Modules\Accounts\Policies\Company;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Credential\Credential;

class CompanyRolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function viewAny(Credential $credential,Company $company)
    {
        if (auth('api')->user()->tokenCan('system_roles_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('system_roles_list_company')){
            return auth('api')->user()->user->company_id == $company->id;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Credential $credential
     * @param CompanyRole $companiesRoles
     * @return mixed
     */
    public function view(Credential $credential, CompanyRole $companiesRoles)
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
     * @return mixed
     */
    public function update(Credential $credential)
    {
        if (auth('api')->user()->tokenCan('system_company_update_all')){
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @param CompanyRole $companiesRoles
     * @return mixed
     */
    public function delete(Credential $credential, CompanyRole $companiesRoles)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param CompanyRole $companiesRoles
     * @return mixed
     */
    public function restore(Credential $credential, CompanyRole $companiesRoles)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param CompanyRole $companiesRoles
     * @return mixed
     */
    public function forceDelete(Credential $credential, CompanyRole $companiesRoles)
    {
        //
    }
}
