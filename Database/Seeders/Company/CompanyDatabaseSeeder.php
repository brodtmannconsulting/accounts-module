<?php

namespace Modules\Accounts\Database\Seeders\Company;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Company\Company;

class CompanyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Company::factory ()->times(3)->create();
    }
}
