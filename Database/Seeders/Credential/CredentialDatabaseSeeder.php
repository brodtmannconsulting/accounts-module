<?php

namespace Modules\Accounts\Database\Seeders\Credential;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;

class CredentialDatabaseSeeder extends Seeder
{
    protected $username;
    protected $password;
    protected $AES_256_username;

    /**
     * CredentialSeeder constructor.
     * @param $username
     * @param $password
     * @param $AES_256_username
     */

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_id = User::factory()->create ([
            'first_name' => 'Maxim',
            'last_name' => 'Primak'
        ])->id;
        Credential::factory ()->create([
            'user_id' => $user_id,
            'username' => 'maxprimak',
            'password' => '123456789',
        ]);

        $user_id = User::factory()->create ()->id;
        Credential::factory ()->create([
            'user_id' => $user_id,
            'username' => 'not_admin',
            'password' => '123456789',
        ]);

        $user_id = User::factory()->create ([
            'first_name' => 'Marc',
            'last_name' => 'Lammerding'
        ])->id;
        Credential::factory ()->create([
            'user_id' => $user_id,
            'username' => 'marc',
            'password' => '123456789',
        ]);
    }
}
