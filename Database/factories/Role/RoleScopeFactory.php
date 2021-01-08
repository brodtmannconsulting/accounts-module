<?php
namespace Modules\Accounts\Database\factories\Role;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Scope\Scope;

class RoleScopeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoleScope::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'scope_id' => Scope::all ()->random ()->id,
            'role_id' => Role::all ()->random ()->id,
            'company_id' => Company::all ()->random ()->id,
        ];
    }
}

