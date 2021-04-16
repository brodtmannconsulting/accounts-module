<?php

namespace Modules\Accounts\Tests\Unit;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Tests\PassportTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends PassportTestCase
{

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp ();
        Artisan::call('passport:install');
    }
    /**
     * @test
     * @return void
     */
    public function a_user_can_log_in()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('api/auth/login', $this->requestData());
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     * @test
     * @return void
     */
    public function a_user_can_not_log_in_with_wrong_password()
    {
        $response = $this->post('api/auth/login', array_merge($this->requestData(), ['password' => '12345678923']));
        $response->assertStatus (Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     * @return void
     */
    public function a_user_can_not_log_in_with_not_existing_credentials()
    {
        $response = $this->post('api/auth/login', array_merge($this->requestData(), ['username' => 'does_not_exists']));
        $response->assertStatus (Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     * @return void
     */
    public function a_user_can_not_log_in_with_not_login_not_allowed()
    {

        $users = User::all();
        $user = $users->filter(function ($user) {
            return $user->full_name == 'Maxim Primak';
        })->first();
        $user->allow_log_in = 0;
        $user->save();
        $response = $this->post('api/auth/login', array_merge($this->requestData()));
        $response->assertStatus (Response::HTTP_UNAUTHORIZED);
    }

    private function requestData() {
        return [
            'username' => 'maxprimak',
            'password' => '123456789',
            'g-recaptcha-response' => '12312'
        ];
    }
}
