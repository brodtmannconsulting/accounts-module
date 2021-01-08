<?php

namespace Modules\Accounts\Policies\Company;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny()
    {
        if (auth('api')->user()->tokenCan('system_company_list_all')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function view(Credential $credential, Company $company)
    {
        if (auth('api')->user()->tokenCan('system_company_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('system_company_list_company')){
            return auth('api')->user()->user->company_id == $company->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create()
    {
        if (auth('api')->user()->tokenCan('system_company_add')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function update(Credential $credential, Company $company)
    {
        if (auth('api')->user()->tokenCan('system_company_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('system_company_update_company')){
            return auth('api')->user()->user->company_id == $company->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function delete(Credential $credential)
    {
        if (auth('api')->user()->tokenCan('system_company_destroy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function restore(Credential $credential, Company $company)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function forceDelete(Credential $credential, Company $company)
    {
        //
    }
}
