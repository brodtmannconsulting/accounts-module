<?php
namespace Modules\Accounts\Database\factories\Credential;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;

class CredentialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Accounts\Entities\Credential\Credential::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;

        return [
            'id' => Str::random (32),
            'user_id' => User::all()->random ()->id,
            'username' => Hash::make($username),
            'password' => Hash::make($password),
            'AES_256_username' => encrypt ($username),
            'valid_from' => now (),
            'valid_until' => now ()->addDay (2),
            'username_type' => Credential::getUsernameType ($username),
        ];
    }
}

