<?php

use Illuminate\Database\Seeder;
use Modules\Accounts\Entities\Auth\TokenKey;

class TokenKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TokenKey::factory()->create ();
    }
}
