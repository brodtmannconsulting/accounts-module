<?php
namespace Modules\Accounts\Database\factories\Role;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Accounts\Entities\Role\Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::random (32),
            'name' => $this->faker->randomElement (['admin','user','super_user']),
            'description' => $this->faker->realText (),
            'is_custom' => 0,
        ];
    }
}

