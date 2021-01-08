<?php

namespace Modules\Accounts\Policies\Credential;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;

class CredentialPolicy
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
        if(auth('api')->user()->tokenCan('user_account_credentials_list_all')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param Credential $credential
     * @param Company $company
     * @return mixed
     */
    public function viewAnyOfCompany(Credential $credential,Company $company)
    {
        if(auth('api')->user()->tokenCan('user_account_credentials_list_all')){
            return true;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_list_company')){
            return auth('api')->user()->user->company_id == $company->id;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  Credential  $credential
     * @param  Credential  $new_credential
     * @return mixed
     */
    public function view(Credential $credential, $new_credential)
    {
        if(auth('api')->user()->tokenCan('user_account_credentials_list_all')){
            return true;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_list_company')){
            return auth('api')->user()->user->company_id == $new_credential->user->company_id;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_list_self')){
            return auth('api')->user()->user->id == $new_credential->user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Credential $credential
     * @param  User $user
     * @return mixed
     */
    public function create(Credential $credential, User $user)
    {
        if(auth('api')->user()->tokenCan('user_account_credentials_add_all')){
            return true;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_add_company')){
            return auth('api')->user()->user->company_id == $user->company_id;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_add_self')){
            return auth('api')->user()->user->id == $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Credential $credential
     * @param Credential $updated_credential
     * @return mixed
     */
    public function update(Credential $credential,Credential $updated_credential)
    {
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_all')){
            return true;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_company')){
            return auth('api')->user()->user->company_id == $updated_credential->user->company_id;
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_self')){
            return auth('api')->user()->user->id == $updated_credential->user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Credential $credential
     * @param Credential $deleted_credential
     * @return mixed
     */
    public function delete(Credential $credential,Credential $deleted_credential)
    {
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_all')){
            return $this->checkIfCredentialIsNotLast($deleted_credential);
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_company')){
            if($this->checkIfCredentialIsNotLast($deleted_credential)){
                return auth('api')->user()->user->company_id == $deleted_credential->user->company_id;
            }
        }
        if(auth('api')->user()->tokenCan('user_account_credentials_reset_self')){
            if($this->checkIfCredentialIsNotLast($deleted_credential)){
                return auth('api')->user()->user->id == $deleted_credential->user->id;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function restore(Credential $credential)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Credential $credential
     * @return mixed
     */
    public function forceDelete(Credential $credential)
    {
        //
    }

    /**
     * @param Credential $deleted_credential
     * @return bool
     */
    private function checkIfCredentialIsNotLast(Credential $deleted_credential)
    {
        return $deleted_credential->user->credentials()->count() > 1;
    }
}
