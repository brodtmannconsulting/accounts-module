<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Tests\PassportTestCase;

class UserPolicyTest extends PassportTestCase
{
    use RefreshDatabase;

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_see_all_users()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get ('api/users');
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_see_all_users()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');

        Passport::actingAs($user->credentials()->first(),[$this->user->getScopes()]);
        $response = $this->get ('api/users');
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_see_users_of_his_company()
    {
        $this->withoutExceptionHandling ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes ());
        $response = $this->get ('api/company_users/'.$this->company->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_see_users_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get ('api/company_users/'.$this->company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_create_users_for_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());

        $response = $this->post('api/users',array_merge ($this->requestData(),['first_name' => 'Maximus', 'company_id' => $this->company->id]));
        $response->assertStatus (Response::HTTP_CREATED)->assertJson([
            'data' => [
                'first_name' => 'Maximus'
            ]
        ]);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_create_users_for_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users',array_merge ($this->requestData(),['first_name' => 'Maximus', 'company_id' => $new_company->id]));
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_create_users_for_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users',array_merge ($this->requestData(),['first_name' => 'Maximus', 'company_id' => $this->company->id]));
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_create_users_for_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users',array_merge ($this->requestData(),['first_name' => 'Maximus', 'company_id' => $new_company->id]));
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_get_user_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_his_company = self::createUserWithRole('another_user','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/users/'.$user_of_his_company->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_get_user_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_another_company = self::createUserWithRole('another_user','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/users/'.$user_of_another_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_get_himself()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/users/'.$user->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_get_another_user_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_his_company = self::createUserWithRole('another_user','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/users/'.$user_of_his_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_get_another_user_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_another_company = self::createUserWithRole('another_user','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/users/'.$user_of_another_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_update_users_for_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_his_company = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users/'.$user_of_his_company->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_update_users_for_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_another_company = self::createUserWithRole('another_user_of_company','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users/'.$user_of_another_company->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_update_himself()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users/'.$user->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_update_users_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_his_company = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users/'.$user_of_his_company->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_update_users_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_another_company = self::createUserWithRole('another_user_of_company','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/users/'.$user_of_another_company->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_update_allow_log_in_of_users_from_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_his_company = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('api/update_allow_log_in/'.$user_of_his_company->id,['allow_log_in' => '0']);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_update_allow_log_in_of_users_from_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_another_company = self::createUserWithRole('another_user_of_company','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('api/update_allow_log_in/'.$user_of_another_company->id,['allow_log_in' => '0']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_update_allow_log_in_of_other_users()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $another_user = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('api/update_allow_log_in/'.$another_user->id,['allow_log_in' => '0']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_delete_users_for_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_his_company = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/users/'.$user_of_his_company->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_delete_users_for_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_of_another_company = self::createUserWithRole('another_user_of_company','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/users/'.$user_of_another_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_delete_users_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_his_company = self::createUserWithRole('another_user_of_company','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/users/'.$user_of_his_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_delete_users_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_of_another_company = self::createUserWithRole('another_user_of_company','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/users/'.$user_of_another_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function no_one_can_delete_themselves()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/users/'.$user->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }


    private function requestData()
    {
        return [
            'first_name' => 'Test_first_name',
            'last_name' => 'Test_last_name',
            'company_id' => Company::factory()->create ()->id,
            'credentials' => [
                [
                    'username' => 'test_user_name',
                    'password' => 'Test12345678',
                    'password_confirmation' => 'Test12345678',
                    'valid_until' => '2019/12/04',
                ],
                [
                    'username' => 'test_username@test.com',
                    'password' => 'Test12345678',
                    'password_confirmation' => 'Test12345678',
                    'valid_until' => '2019/12/04',
                ],
            ],
        ];
    }
}
