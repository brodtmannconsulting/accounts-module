<?php

namespace Modules\Accounts\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Policies\Company\CompanyPolicy;
use Modules\Accounts\Policies\Company\CompanyRolePolicy;
use Modules\Accounts\Policies\Credential\CredentialPolicy;
use Modules\Accounts\Policies\Role\RolePolicy;
use Modules\Accounts\Policies\Role\RoleScopePolicy;
use Modules\Accounts\Policies\Role\UserRolePolicy;
use Modules\Accounts\Policies\User\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        CompanyRole::class => CompanyRolePolicy::class,
        Company::class => CompanyPolicy::class,
        Credential::class => CredentialPolicy::class,
        User::class => UserPolicy::class,
        UserRole::class => UserRolePolicy::class,
        RoleScope::class => RoleScopePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'user_account_list_self'=> 'Can access show in UsersController and get only own user',
            'user_account_list_company'=> 'Can access show and indexCompany in UsersController and get only users of his company',
            'user_account_list_all'=> 'Can access show, indexCompany and index in UsersController and get all users',
            'user_account_add_company'=> 'Access To store in UsersController and can create only users for his company',
            'user_account_add_all'=> 'Access To store in UsersController and can create users for all companies',
            'user_account_destroy_company'=> 'Access To delete in UsersController and can delete users from his company',
            'user_account_destroy_all'=> 'Access To delete in UsersController and can delete all users',
            'user_account_update_self'=> 'Access To update method In UsersController and can update only own user',
            'user_account_update_company'=> 'Access To update method In UsersController and can update only users of his company',
            'user_account_update_all'=> 'Access To update method In UsersController and can update all users',
            'user_account_allowLogin_update_company'=> 'Access To updateAllowLogIn In UsersController and can update allow_log_in only for users of own company',
            'user_account_allowLogin_update_all'=> 'Access To updateAllowLogIn In UsersController and can update allow_log_in for all users',

//        For RolesController
            'system_roles_list_company'=> 'Can access show and indexCompany in RolesController and get only roles for company',
            'system_roles_list_all'=> 'Can access show, indexCompany and index in RolesController and get all roles',
            'system_roles_add_company'=> 'Can access store in RolesController and create only roles for own company',
            'system_roles_add_all'=> 'Can access store in RolesController and can create roles for all companies',
            'system_roles_update_company'=> 'Can access update in RolesController and update only roles for own company',
            'system_roles_update_all'=> 'Can access update in RolesController and update roles for all companies',
            'system_roles_destroy_company'=> 'Can access delete in RolesController and delete only roles of own company',
            'system_roles_destroy_all'=> 'Can access delete in RolesController and delete all roles',

            //For CompanyController
            'system_company_list_all'=> 'Can access show and index in CompanyController and get all companies',
            'system_company_list_company'=> 'Can access show in CompanyController and get own company',
            'system_company_add'=> 'Can access store in CompanyController and create new companies',
            'system_company_destroy'=> 'Can access destroy in CompanyController and delete companies',
            'system_company_update_all'=> 'Can access update in RolesController and update all companies',
            'system_company_update_company'=> 'Can access update in RolesController and update only own companies',

            //For CredentialsController
            'user_account_credentials_list_all'=> 'Can access show and index in CredentialsController and get all credentials',
            'user_account_credentials_list_company'=> 'Can access show in CredentialsController and get credentials of company users',
            'user_account_credentials_list_self'=> 'Can access show in CredentialsController and get credentials of own user',

            'user_account_credentials_add_all'=> 'Can access store in CredentialsController and store credentials for all users',
            'user_account_credentials_add_company'=> 'Can access store in CredentialsController and store credentials only for users of own company',
            'user_account_credentials_add_self'=> 'Can access store in CredentialsController and store credentials only for own user',

            'user_account_credentials_reset_all'=> 'Can access update in CredentialsController and update all credentials',
            'user_account_credentials_reset_company'=> 'Can access update in CredentialsController and update only company users credentials',
            'user_account_credentials_reset_self'=> 'Can access update in CredentialsController and update only own credentials',

            'user_account_credentials_destroy_all'=> 'Can access delete in CredentialsController and delete all credentials',
            'user_account_credentials_destroy_company'=> 'Can access delete CredentialsController and delete company users credentials',
            'user_account_credentials_destroy_self'=> 'Can access delete in CredentialsController and delete own credentials',

            //For UserRolesController
            'user_account_roles_update_company'=> 'Can access update, destroy and store in UserRolesController and add and remove roles to company users',
            'user_account_roles_update_all'=> 'Can access update, destroy and store in UserRolesController and add and remove all roles to all users',

            'role_user_accounts_list_all'=> 'Can access index, show in UserRolesController and get all users of role',
            'role_user_accounts_list_company'=> 'Can access index, show in UserRolesController and get all company users of role',

            // VIVIDLEAF
            'suggestions_list_all' => 'Can Access Suggestions, and see all suggestions of ALL companies',
            'suggestions_list_company' => 'Can Access Suggestions, and see suggestions ONLY of own company',
            'suggestions_create' => 'Can create suggestions',
            'suggestions_update' => 'Can update suggestions',
            'suggestions_delete' => 'Can delete suggestions',
            'questionnaire_list' => 'Can Access Questionnaire, and see ALL saved questions in System',
            'questionnaire_company' => 'Can Access Questionnaire, and see ONLY questions saved by users of own company',
            'answers_create' => 'Can answer questions',
            'answers_update' => 'Can update and delete answers of questions',
            'manage_questions' => 'Can Access Manage Questions Page',
            'manage_questions_create' => 'Can create new Questions of all types',
            'manage_questions_update' => 'Can update questions of all types',
            'manage_questions_delete' => 'Can delete questions of all types',
            'certificates_list' => 'Can Access Certificates Page, and see all Certificates of ALL Companies',
            'certificates_company' => 'Can Access Certificates Page, and see Certificates ONLY of own Company',

        ]);


        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addDays(1));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(7));
    }
}
