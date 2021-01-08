<?php

namespace Modules\Accounts\Database\Seeders\Role;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Role\Role;

class RoleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Role::factory()->create (['name' => 'super_user']);
        Role::factory()->create (['name' => 'admin']);
        Role::factory()->create (['name' => 'user']);

        $this->call (UserRoleTableSeeder::class);
    }
}
