<?php
namespace Modules\Accounts\Database\factories\Company;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;

class CompanyRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model =  \Modules\Accounts\Entities\Company\CompanyRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::all ()->random ()->id,
            'role_id' => Role::all ()->random ()->id,
        ];
    }
}

