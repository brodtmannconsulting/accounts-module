<?php

use Illuminate\Http\Request;
use Modules\Accounts\Http\Controllers\Auth\AuthController;
use Modules\Accounts\Http\Controllers\Role\RolesScopesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api_login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api_logout');
});

//Companies
Route::middleware(['auth:api'])->group(function () {
    Route::get ('/companies','Company\CompanyController@index')->middleware (['scope:system_company_list_all']);
    Route::post ('/companies','Company\CompanyController@store')->middleware (['scope:system_company_add']);
    Route::post ('/companies/{company}','Company\CompanyController@update')->middleware (['scope:system_company_update_all,system_company_update_company']);
    Route::get ('/companies/{company}','Company\CompanyController@show')->middleware (['scope:system_company_list_all,system_company_list_company']);
    Route::delete ('/companies/{company}','Company\CompanyController@destroy')->middleware (['scope:system_company_destroy']);
});

//Users
Route::middleware(['auth:api','scope:user_account_list_self,user_account_list_company,user_account_list_all'])->group(function () {
    Route::get ('/users','User\UsersController@index');
    Route::get ('/company_users/{company_id}','User\UsersController@indexCompany');
    Route::get ('/users/{user}','User\UsersController@show');
});

Route::middleware(['auth:api'])->group(function () {
    Route::post ('/users','User\UsersController@store')->middleware (['scope:user_account_add_company,user_account_add_all']);
    Route::post ('/users/{user}','User\UsersController@update')->middleware (['scope:user_account_update_self,user_account_update_company,user_account_update_all']);
    Route::patch ('/update_allow_log_in/{user}','User\UsersController@updateAllowLogIn')->middleware (['scope:user_account_allowLogin_update_company,user_account_allowLogin_update_all']);
    Route::delete ('/users/{user}','User\UsersController@destroy')->middleware (['scope:user_account_destroy_company,user_account_destroy_all']);
});


//Roles
Route::middleware(['auth:api'])->group(function () {
    Route::get ('/roles','Role\RolesController@index')->middleware (['scope:system_roles_list_all']);
    Route::post ('/roles','Role\RolesController@store')->middleware (['scope:system_roles_add_company,system_roles_add_all']);
    Route::patch ('/roles/{role}','Role\RolesController@update')->middleware (['scope:system_roles_update_company,system_roles_update_all']);
    Route::get ('/roles/{role}','Role\RolesController@show')->middleware (['scope:system_roles_list_company,system_roles_list_all']);
    Route::delete ('/roles/{role}','Role\RolesController@destroy')->middleware (['scope:system_roles_destroy_company,system_roles_destroy_all']);
});

//Credentials
Route::middleware(['auth:api'])->group(function () {
    Route::get ('/credentials','Credential\CredentialController@index')->middleware (['scope:user_account_credentials_list_all']);
    Route::get ('/company_credentials/{company}','Credential\CredentialController@indexCompany')->middleware (['scope:user_account_credentials_list_all,user_account_credentials_list_company']);
    Route::post ('/credentials','Credential\CredentialController@store')->middleware (['scope:user_account_credentials_add_all,user_account_credentials_add_company,user_account_credentials_add_self']);
    Route::patch ('/credentials/{credential}','Credential\CredentialController@update')->middleware (['scope:user_account_credentials_reset_all,user_account_credentials_reset_company,user_account_credentials_reset_self']);
    Route::get ('/credentials/{credential}','Credential\CredentialController@show')->middleware (['scope:user_account_credentials_list_all,user_account_credentials_list_company,user_account_credentials_list_self']);
    Route::delete ('/credentials/{credential}','Credential\CredentialController@destroy')->middleware (['scope:user_account_credentials_destroy_all,user_account_credentials_destroy_company,user_account_credentials_destroy_self']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get ('/company_roles/{company_id}','Company\CompaniesRolesController@index')->middleware (['scope:system_roles_list_company,system_roles_list_all']);
    Route::post ('/company_roles/role/{company_id}','Company\CompaniesRolesController@show')->middleware (['scope:system_roles_list_company,system_roles_list_all']);
    Route::patch ('/company_roles/{company}','Company\CompaniesRolesController@update')->middleware (['scope:system_company_update_all']);
    Route::post ('/company_roles/delete/{company}','Company\CompaniesRolesController@destroy')->middleware (['scope:system_company_update_all']);
});

//UsersRolesUsersRolesController
Route::middleware(['auth:api'])->group(function () {
    Route::post ('/users_roles','User\UsersRolesController@store')->middleware (['scope:user_account_roles_update_company,user_account_roles_update_all']);
    Route::post ('/users_roles/delete','User\UsersRolesController@destroy')->middleware (['scope:user_account_roles_update_company,user_account_roles_update_all']);
    Route::patch ('/users_roles/{role}','User\UsersRolesController@update')->middleware (['scope:user_account_roles_update_company,user_account_roles_update_all']);
    Route::post ('/users_roles/role/{role}','User\UsersRolesController@show')->middleware (['scope:role_user_accounts_list_company,role_user_accounts_list_all']);
});

//RolesScopes
Route::middleware(['auth:api'])->group(function () {
    Route::patch ('/role_scopes/{role}',[RolesScopesController::class, 'update'])->middleware (['scope:user_account_roles_update_company,user_account_roles_update_all']);
    Route::post ('/role_scopes/delete/{role}',[RolesScopesController::class, 'destroy'])->middleware (['scope:user_account_roles_update_company,user_account_roles_update_all']);
});

//UserTokenController
Route::middleware(['auth:api'])->group(function () {
    Route::post ('/user_from_token','Auth\UserTokenController@show');
});

Route::get ('/user', 'Auth\AuthenticatedUserController@index');

Route::get('/publicKey/{time}', 'Auth\PublicKeyController@show');

