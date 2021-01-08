<?php
namespace Modules\Accounts\Database\Seeders\Company;

use Illuminate\Database\Seeder;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Role\Role;

class CompanyRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::all ();
        $roles = Role::all ();
        foreach ($companies as $company){
            foreach ($roles as $role){
                CompanyRole::factory ()->create (['company_id' => $company->id,'role_id' => $role]);
            }
        }
    }
}
