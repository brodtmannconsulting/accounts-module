<?php

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Database\Seeders\Company\CompanyDatabaseSeeder;
use Modules\Accounts\Database\Seeders\Company\CompanyRolesSeeder;
use Modules\Accounts\Database\Seeders\Credential\CredentialDatabaseSeeder;
use Modules\Accounts\Database\Seeders\Role\RoleDatabaseSeeder;
use Modules\Accounts\Database\Seeders\Role\UserRoleTableSeeder;
use Modules\Accounts\Database\Seeders\Scope\ScopeDatabaseSeeder;

class AccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(CompanyDatabaseSeeder::class);
        $this->call(CredentialDatabaseSeeder::class);
        $this->call(ScopeDatabaseSeeder::class);
        $this->call(RoleDatabaseSeeder::class);
        $this->call(CompanyRolesSeeder::class);
        $this->call(UserRoleTableSeeder::class);
    }
}
