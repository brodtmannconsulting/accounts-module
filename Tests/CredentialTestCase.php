<?php
namespace Modules\Accounts\Tests;

use Carbon\Carbon;

class CredentialTestCase extends PassportTestCase
{
    public function requestData()
    {
        return [
            'user_id' => $this->user->id,
            'username' =>  'New_user_credential',
            'password' =>  'Test1234567',
            'password_confirmation' =>  'Test1234567',
            'valid_until' => Carbon::now ()->addDays (3)->format('d-m-Y H:i'),
            ];
    }

    public function credentialData()
    {
        $credential = $this->user->credentials ()->first();
        return [
            'data' => [
                'credential_id' => $credential->id,
                'username' => decrypt ($credential->AES_256_username),
                'username_type' => $credential->username_type,
                'valid_from_for_humans' => $credential->valid_from->format('d-m-Y H:i'),
                'valid_from' => $credential->valid_from->format('d-m-Y H:i'),
                'valid_until_for_humans' => $credential->valid_until->format('d-m-Y H:i'),
                'valid_until' => $credential->valid_until->format('d-m-Y H:i'),
                'created_at_for_humans' => $credential->created_at->format('d-m-Y H:i'),
                'created_at' => $credential->created_at->format('d-m-Y H:i'),
            ],
            'links' => [
                'self' => $credential->path()
            ]
        ];
    }

}
