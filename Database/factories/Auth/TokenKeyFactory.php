<?php
namespace Modules\Accounts\Database\factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Auth\TokenKey as TokenKeyAlias;

class TokenKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TokenKeyAlias::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'public_key' => env('PASSPORT_PUBLIC_KEY'),
            'private_key' => env('PASSPORT_PRIVATE_KEY'),
        ];
    }
}

