<?php
namespace Modules\Accounts\Database\factories\Role;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\User\User;

class UserRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::all ()->random ()->id,
            'role_id' => Role::all()->random()->id,
        ];
    }
}

