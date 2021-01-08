<?php

namespace Modules\Accounts\Database\Seeders\Role;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\Scope\Scope;


class UserRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        //For Max Primak
        $credential = Credential::where('username',Credential::getHashedUsername ('maxprimak'))->first();
        $super_user_role = Role::where('name','super_user')->first();
        UserRole::factory ()->create (['user_id' => $credential->user->id, 'role_id' => $super_user_role->id]);

        $scopes = Scope::all ();
        $companies = Company::all ();
        foreach ($scopes as $scope){
            foreach ($companies as $company){
                RoleScope::factory()->create ([
                    'scope_id' => $scope->id,
                    'role_id' =>  $super_user_role->id,
                    'company_id' => $company->id
                ]);
            }
        }

//        For Marc Lammerding
        $credential = Credential::where('username',Credential::getHashedUsername ('marc'))->first();
        UserRole::factory()->create (['user_id' => $credential->user->id, 'role_id' => $super_user_role->id]);
    }
}
