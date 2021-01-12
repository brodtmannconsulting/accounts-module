<?php
namespace Modules\Accounts\Database\factories\Company;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Accounts\Entities\Company\Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::random(32),
            'name' => $this->faker->company,
            'description' => 'description',
            'company_website' => $this->faker->url,
        ];
    }
}

