<?php

namespace Modules\Accounts\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\Scope\Scope;
use Modules\Accounts\Entities\User\User;
use Tests\TestCase;

class PassportTestCase extends TestCase
{
    use RefreshDatabase;

    public $scopes = [];
    protected $user;
    protected $company;
    protected $credential;
    protected $username;
    protected $password;
    protected $super_user_role;
    protected $user_account_list_self;
    protected $user_account_list_company;
    protected $user_account_list_all;
    protected $user_account_add_company;
    protected $user_account_add_all;
    protected $user_account_destroy_company;
    protected $user_account_destroy_all;
    protected $user_account_update_self;
    protected $user_account_update_company;
    protected $user_account_update_all;
    protected $user_account_allowLogin_update_company;
    protected $user_account_allowLogin_update_all;
    protected $system_roles_list_all;
    protected $system_roles_add_all;
    protected $system_roles_update_all;
    protected $system_roles_destroy_all;
    protected $system_company_list_all;
    protected $system_company_add;
    protected $system_company_destroy;
    protected $system_company_update_all;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp ();
        $this->artisan ('tokenKeys:cron');
        //Set Up For 1 user maxprimak
        $this->username = 'maxprimak';
        $this->password = '123456789';
        $this->company = Company::factory()->create();
        $this->user = $this->createUserWithRole ($this->username, $this->password, $this->company->id, 'super_user');
        Passport::tokensCan (Scope::getAllScopesAsArray ());
    }


    /**
     * @param string $username
     * @param string $password
     * @param string $company_id
     * @param string $role_name
     * @param bool $is_custom
     * @return User
     */
    public function createUserWithRole(string $username, string $password, string $company_id, string $role_name, bool $is_custom = false)
    {
        $user = self::createUserWithCredential ($company_id, $username, $password);
        $role = self::createRole ($role_name, $company_id, $user->id, $is_custom);
        $scopes = array();
        if ($role->name == 'super_user') {
            array_push ($scopes, $this->createScopesForUsersControllerForSuperUser ($this->company->id, $role->id));
            array_push ($scopes, $this->createScopesForRolesControllerForSuperUser ($this->company->id, $role->id));
            array_push ($scopes, $this->createScopesForCompanyControllerForSuperUser ($this->company->id, $role->id));
            array_push ($scopes, $this->createScopesForCredentialControllerForSuperUser ($this->company->id, $role->id));
            array_push ($scopes, $this->createScopesForUsersRolesControllerForSuperUser ($this->company->id, $role->id));
        }
        if ($role->name == 'admin') {
            array_push ($scopes, $this->createScopesForCompanyControllerForAdmin ($company_id, $role->id));
            array_push ($scopes, $this->createScopesForCredentialControllerForAdmin ($company_id, $role->id));
            array_push ($scopes, $this->createScopesForRolesControllerForAdmin ($company_id, $role->id));
            array_push ($scopes, $this->createScopesForUsersControllerForAdmin ($company_id, $role->id));
        }
        if ($role->name == 'user') {
            array_push ($scopes, $this->createScopesForCredentialControllerForUser ($company_id, $role->id));
            array_push ($scopes, $this->createScopesForUsersControllerForUser ($company_id, $role->id));
        }
        $scopes = $this->array_flatten ($scopes);
        $this->loginUser ($user->credentials ()->first (), $scopes);
        return $user;
    }

    /**
     * @param string $company_id
     * @param string $username
     * @param string $password
     * @return User
     */
    static function createUserWithCredential(string $company_id, string $username, string $password)
    {

        $user = User::factory()->create ([
                'first_name' => 'Maxim',
                'last_name' => 'Primak',
                'company_id' => $company_id]
        );
        $credential = Credential::factory()->create (self::getCredentialsData ($user, $username, $password));
        return $user;
    }

    /**
     * @param $user
     * @param $username
     * @param $password
     * @return array
     */
    static function getCredentialsData($user, $username, $password)
    {
        return [
            'user_id' => $user->id,
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * @param string $name
     * @param string $company_id
     * @param string $user_id
     * @param bool $is_custom
     * @return Role
     */
    public function createRole(string $name, string $company_id, string $user_id, bool $is_custom = false)
    {
        $role = Role::factory()->create (['name' => $name, 'is_custom' => $is_custom]);
        CompanyRole::factory()->create ([
            'company_id' => $company_id,
            'role_id' => $role->id
        ]);
        UserRole::factory()->create (['user_id' => $user_id, 'role_id' => $role->id]);
        return $role;
    }

    /**
     * @param string $company_id
     * @param string $role_id
     * @return array
     */
    public function createScopesForUsersControllerForSuperUser(string $company_id, string $role_id)
    {
        $scopes = array();
        $this->user_account_list_all = self::createScope ('user_account_list_all', $company_id, $role_id);
        $this->user_account_add_all = self::createScope ('user_account_add_all', $company_id, $role_id);
        $this->user_account_destroy_all = self::createScope ('user_account_destroy_all', $company_id, $role_id);
        $this->user_account_update_all = self::createScope ('user_account_update_all', $company_id, $role_id);
        $this->user_account_allowLogin_update_all = self::createScope ('user_account_allowLogin_update_all', $company_id, $role_id);

        array_push ($scopes, $this->user_account_list_all->name);
        array_push ($scopes, $this->user_account_add_all->name);
        array_push ($scopes, $this->user_account_destroy_all->name);
        array_push ($scopes, $this->user_account_update_all->name);
        array_push ($scopes, $this->user_account_allowLogin_update_all->name);
        return $scopes;
    }

    /**
     * @param string $name
     * @param string $company_id
     * @param string $role_id
     * @return Scope
     */
    public static function createScope(string $name, string $company_id, string $role_id)
    {
        $scope = Scope::factory()->create (['name' => $name]);
        self::addScopeToSpecificRole ($scope, $company_id, $role_id);
        return $scope;
    }

    /**
     * @param Scope $scope
     * @param string $company_id
     * @param string $role_id
     */
    public static function addScopeToSpecificRole(Scope $scope, string $company_id, string $role_id)
    {
        RoleScope::factory()->create ([
            'scope_id' => $scope->id,
            'role_id' => $role_id,
            'company_id' => $company_id
        ]);
    }

    public function createScopesForRolesControllerForSuperUser(string $company_id, string $role_id)
    {
        $scopes = array();
        $this->system_roles_list_all = self::createScope ('system_roles_list_all', $company_id, $role_id);
        $this->system_roles_add_all = self::createScope ('system_roles_add_all', $company_id, $role_id);
        $this->system_roles_update_all = self::createScope ('system_roles_update_all', $company_id, $role_id);
        $this->system_roles_destroy_all = self::createScope ('system_roles_destroy_all', $company_id, $role_id);
        array_push ($scopes, $this->system_roles_list_all->name);
        array_push ($scopes, $this->system_roles_add_all->name);
        array_push ($scopes, $this->system_roles_update_all->name);
        array_push ($scopes, $this->system_roles_destroy_all->name);
        return $scopes;
    }

    public function createScopesForCompanyControllerForSuperUser($company_id, $role_id)
    {
        $scopes = array();
        $this->system_company_list_all = self::createScope ('system_company_list_all', $company_id, $role_id);
        $this->system_company_add = self::createScope ('system_company_add', $company_id, $role_id);
        $this->system_company_destroy = self::createScope ('system_company_destroy', $company_id, $role_id);
        $this->system_company_update_all = self::createScope ('system_company_update_all', $company_id, $role_id);

        array_push ($scopes, $this->system_company_list_all->name);
        array_push ($scopes, $this->system_company_add->name);
        array_push ($scopes, $this->system_company_destroy->name);
        array_push ($scopes, $this->system_company_update_all->name);

        return $scopes;
    }

    public function createScopesForCredentialControllerForSuperUser($company_id, $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('user_account_credentials_list_all', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_add_all', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_reset_all', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_destroy_all', $company_id, $role_id)->name);
        return $scopes;
    }

    public function createScopesForUsersRolesControllerForSuperUser($company_id, $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('user_account_roles_update_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_roles_update_all', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('role_user_accounts_list_all', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('role_user_accounts_list_company', $company_id, $role_id)->name);
        return $scopes;
    }

    public function createScopesForCompanyControllerForAdmin($company_id, $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('system_company_list_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('system_company_update_company', $company_id, $role_id)->name);
        return $scopes;
    }

    public function createScopesForCredentialControllerForAdmin($company_id, $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('user_account_credentials_list_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_add_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_reset_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_destroy_company', $company_id, $role_id)->name);
        return $scopes;
    }

    public function createScopesForRolesControllerForAdmin(string $company_id, string $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('system_roles_list_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('system_roles_add_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('system_roles_update_company', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('system_roles_destroy_company', $company_id, $role_id)->name);
        return $scopes;
    }

    /**
     * @param $company_id
     * @param $role_id
     * @return array
     */
    public function createScopesForUsersControllerForAdmin($company_id, $role_id)
    {
        $scopes = array();
        $this->user_account_list_company = self::createScope ('user_account_list_company', $company_id, $role_id);
        $this->user_account_add_company = self::createScope ('user_account_add_company', $company_id, $role_id);
        $this->user_account_destroy_company = self::createScope ('user_account_destroy_company', $company_id, $role_id);
        $this->user_account_update_company = self::createScope ('user_account_update_company', $company_id, $role_id);
        $this->user_account_allowLogin_update_company = self::createScope ('user_account_allowLogin_update_company', $company_id, $role_id);

        array_push ($scopes, $this->user_account_list_company->name);
        array_push ($scopes, $this->user_account_add_company->name);
        array_push ($scopes, $this->user_account_destroy_company->name);
        array_push ($scopes, $this->user_account_update_company->name);
        array_push ($scopes, $this->user_account_allowLogin_update_company->name);
        return $scopes;
    }

    public function createScopesForCredentialControllerForUser($company_id, $role_id)
    {
        $scopes = array();
        array_push ($scopes, self::createScope ('user_account_credentials_list_self', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_add_self', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_reset_self', $company_id, $role_id)->name);
        array_push ($scopes, self::createScope ('user_account_credentials_destroy_self', $company_id, $role_id)->name);
        return $scopes;
    }

    /**
     * @param $company_id
     * @param $role_id
     * @return array
     */
    public function createScopesForUsersControllerForUser($company_id, $role_id)
    {
        $scopes = array();
        $this->user_account_list_self = self::createScope ('user_account_list_self', $company_id, $role_id);
        $this->user_account_update_self = self::createScope ('user_account_update_self', $company_id, $role_id);

        array_push ($scopes, $this->user_account_list_self->name);
        array_push ($scopes, $this->user_account_update_self->name);
        return $scopes;
    }

    /**
     * @param $array
     * @return array|bool
     */
    private function array_flatten($array)
    {
        if (!is_array ($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array ($value)) {
                $result = array_merge ($result, $this->array_flatten ($value));
            } else {
                $result = array_merge ($result, array($key => $value));
            }
        }
        return $result;
    }

    /**
     * @param Credential $credential
     * @param array $scopes
     * @return bool
     */
    public function loginUser(Credential $credential, array $scopes)
    {
        Passport::actingAs ($credential, $scopes);
        return true;
    }

}
