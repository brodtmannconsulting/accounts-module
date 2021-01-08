<?php
namespace Modules\Accounts\Database\factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\User\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'company_id' => Company::all ()->random ()->id,
            'allow_log_in' => 1,
        ];
    }
}

