<?php
namespace Modules\Accounts\Database\factories\Scope;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Accounts\Entities\Scope\Scope;

class ScopeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Scope::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::random(32),
            'name' => $this->faker->domainName,
            'description' => $this->faker->text
        ];
    }
}

