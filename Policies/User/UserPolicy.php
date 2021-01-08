<?php

namespace Modules\Accounts\Policies\User;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine whether the user can view any models.
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function viewAnyOfCompany(Credential $credential,Company $company)
    {
        if(auth('api')->user()->tokenCan('user_account_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_list_company')){
            return auth('api')->user()->user->company_id == $company->id;
        }
        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function viewAny(Credential $credential)
    {
        return auth('api')->user()->tokenCan('user_account_list_all');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function create(Credential $credential)
    {
        if (auth('api')->user()->tokenCan('user_account_add_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_add_company')){
            return auth('api')->user()->user->company_id == request ()->company_id;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Credential $credential
     * @param User $user
     * @return mixed
     */
    public function view(Credential $credential,User $user)
    {
        if(auth('api')->user()->tokenCan('user_account_list_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_list_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }
        if(auth('api')->user()->tokenCan('user_account_list_self')){
            return auth('api')->user()->user->id == $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param User $user
     * @return mixed
     */
    public function update(Credential $credential,User $user)
    {
        if(auth('api')->user()->tokenCan('user_account_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_update_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }
        if(auth('api')->user()->tokenCan('user_account_update_self')){
            return auth('api')->user()->user->id == $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param User $user
     * @return mixed
     */
    public function updateAllowLogIn(Credential $credential,User $user)
    {
        if(auth('api')->user()->tokenCan('user_account_allowLogin_update_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_allowLogin_update_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
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
    public function delete(Credential $credential,User $user)
    {

        if(auth('api')->user()->user->id == $user->id){
            return false;
        }
        if (auth('api')->user()->tokenCan('user_account_destroy_all')){
            return true;
        }
        if (auth('api')->user()->tokenCan('user_account_destroy_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @param  User $user
     * @return mixed
     */
    public function restore(Credential $credential,User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @param  User $user
     * @return mixed
     */
    public function forceDelete(Credential $credential,User $user)
    {
        return false;
    }
}
