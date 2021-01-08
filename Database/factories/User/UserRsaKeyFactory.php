<?php
namespace Modules\Accounts\Database\factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Entities\User\UserRsaKey;

class UserRsaKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserRsaKey::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $rsa_key_id = Str::random(32);

        //Generate public and private key
        $res = openssl_pkey_new(array('digest_alg' => 'sha1', 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048));
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $private_key = $privKey;
        $public_key = $pubKey["key"];
        //encrypt private key with app key
        $encrypted_private_key = encrypt ($private_key);

        return [
            'id' => $rsa_key_id,
            'user_id' => User::all()->random()->id,
            'public_key' => $public_key,
            'private_key' => $encrypted_private_key,
            'valid_from' =>now (),
            'valid_until' =>now ()->addDay (2),
        ];
    }
}

