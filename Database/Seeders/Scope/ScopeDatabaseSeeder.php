<?php

namespace Modules\Accounts\Database\Seeders\Scope;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Scope\Scope;

class ScopeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        //        For UsersController
        Scope::factory()->create (['name' => 'user_account_list_self','description'=> 'Can access show in UsersController and get only own user']);
        Scope::factory()->create (['name' => 'user_account_list_company','description'=> 'Can access show and indexCompany in UsersController and get only users of his company']);
        Scope::factory()->create (['name' => 'user_account_list_all','description'=> 'Can access show, indexCompany and index in UsersController and get all users']);
        Scope::factory()->create (['name' => 'user_account_add_company','description'=> 'Access To store in UsersController and can create only users for his company']);
        Scope::factory()->create (['name' => 'user_account_add_all','description'=> 'Access To store in UsersController and can create users for all companies']);
        Scope::factory()->create (['name' => 'user_account_destroy_company','description'=> 'Access To delete in UsersController and can delete users from his company']);
        Scope::factory()->create (['name' => 'user_account_destroy_all','description'=> 'Access To delete in UsersController and can delete all users']);
        Scope::factory()->create (['name' => 'user_account_update_self','description'=> 'Access To update method In UsersController and can update only own user']);
        Scope::factory()->create (['name' => 'user_account_update_company','description'=> 'Access To update method In UsersController and can update only users of his company']);
        Scope::factory()->create (['name' => 'user_account_update_all','description'=> 'Access To update method In UsersController and can update all users']);
        Scope::factory()->create (['name' => 'user_account_allowLogin_update_company','description'=> 'Access To updateAllowLogIn In UsersController and can update allow_log_in only for users of own company']);
        Scope::factory()->create (['name' => 'user_account_allowLogin_update_all','description'=> 'Access To updateAllowLogIn In UsersController and can update allow_log_in for all users']);

//        For RolesController
        Scope::factory()->create (['name' => 'system_roles_list_company','description'=> 'Can access show and indexCompany in RolesController and get only roles for company']);
        Scope::factory()->create (['name' => 'system_roles_list_all','description'=> 'Can access show, indexCompany and index in RolesController and get all roles']);
        Scope::factory()->create (['name' => 'system_roles_add_company','description'=> 'Can access store in RolesController and create only roles for own company']);
        Scope::factory()->create (['name' => 'system_roles_add_all','description'=> 'Can access store in RolesController and can create roles for all companies']);
        Scope::factory()->create (['name' => 'system_roles_update_company','description'=> 'Can access update in RolesController and update only roles for own company']);
        Scope::factory()->create (['name' => 'system_roles_update_all','description'=> 'Can access update in RolesController and update roles for all companies']);
        Scope::factory()->create (['name' => 'system_roles_destroy_company','description'=> 'Can access delete in RolesController and delete only roles of own company']);
        Scope::factory()->create (['name' => 'system_roles_destroy_all','description'=> 'Can access delete in RolesController and delete all roles']);

        //For CompanyController
        Scope::factory()->create (['name' => 'system_company_list_all','description'=> 'Can access show and index in CompanyController and get all companies']);
        Scope::factory()->create (['name' => 'system_company_list_company','description'=> 'Can access show in CompanyController and get own company']);
        Scope::factory()->create (['name' => 'system_company_add','description'=> 'Can access store in CompanyController and create new companies']);
        Scope::factory()->create (['name' => 'system_company_destroy','description'=> 'Can access destroy in CompanyController and delete companies']);
        Scope::factory()->create (['name' => 'system_company_update_all','description'=> 'Can access update in RolesController and update all companies']);
        Scope::factory()->create (['name' => 'system_company_update_company','description'=> 'Can access update in RolesController and update only own companies']);

        //For CredentialsController
        Scope::factory()->create (['name' => 'user_account_credentials_list_all','description'=> 'Can access show and index in CredentialsController and get all credentials']);
        Scope::factory()->create (['name' => 'user_account_credentials_list_company','description'=> 'Can access show in CredentialsController and get credentials of company users']);
        Scope::factory()->create (['name' => 'user_account_credentials_list_self','description'=> 'Can access show in CredentialsController and get credentials of own user']);

        Scope::factory()->create (['name' => 'user_account_credentials_add_all','description'=> 'Can access store in CredentialsController and store credentials for all users']);
        Scope::factory()->create (['name' => 'user_account_credentials_add_company','description'=> 'Can access store in CredentialsController and store credentials only for users of own company']);
        Scope::factory()->create (['name' => 'user_account_credentials_add_self','description'=> 'Can access store in CredentialsController and store credentials only for own user']);

        Scope::factory()->create (['name' => 'user_account_credentials_reset_all','description'=> 'Can access update in CredentialsController and update all credentials']);
        Scope::factory()->create (['name' => 'user_account_credentials_reset_company','description'=> 'Can access update in CredentialsController and update only company users credentials']);
        Scope::factory()->create (['name' => 'user_account_credentials_reset_self','description'=> 'Can access update in CredentialsController and update only own credentials']);

        Scope::factory()->create (['name' => 'user_account_credentials_destroy_all','description'=> 'Can access delete in CredentialsController and delete all credentials']);
        Scope::factory()->create (['name' => 'user_account_credentials_destroy_company','description'=> 'Can access delete CredentialsController and delete company users credentials']);
        Scope::factory()->create (['name' => 'user_account_credentials_destroy_self','description'=> 'Can access delete in CredentialsController and delete own credentials']);

        //For UserRolesController
        //
        Scope::factory()->create (['name' => 'user_account_roles_update_company','description'=> 'Can access update, destroy and store in UserRolesController and add and remove roles to company users']);
        Scope::factory()->create (['name' => 'user_account_roles_update_all','description'=> 'Can access update, destroy and store in UserRolesController and add and remove all roles to all users']);

        Scope::factory()->create (['name' => 'role_user_accounts_list_all','description'=> 'Can access index, show in UserRolesController and get all users of role']);
        Scope::factory()->create (['name' => 'role_user_accounts_list_company','description'=> 'Can access index, show in UserRolesController and get all company users of role']);

    }
}
